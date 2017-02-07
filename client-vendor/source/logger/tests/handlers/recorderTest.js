require(["logger/vendor/src/logger", "logger/handlers/recorder"], function(Logger, Recorder) {

  QUnit.module('logger/handlers/recorder', {
    beforeEach: function() {
      this.recorder = new Recorder();
      var date = this.date = new Date();
      this.recorder._getDate = function() {
        return date;
      };
    }
  });

  QUnit.test("Recorder: substitution/formatting", function(assert) {
    var recorder = this.recorder, date = this.date.toISOString();
    recorder.addRecord(['test %s %i %d %f %o %O', 'foo', 10, 10, 1.1, document.createElement('div'), {foo: 10}], {level: Logger.INFO});
    recorder.addRecord(['test %s %i %d %f %o %x', 'foo', 10, 10, 1.1, document.createElement('div'), {foo: 10}], {level: Logger.INFO});

    assert.equal(recorder.getFormattedRecords(), [
      '[' + date + ' INFO] test foo 10 10 1.1 [object HTMLDivElement] {"foo":10}',
      '[' + date + ' INFO] test foo 10 10 1.1 [object HTMLDivElement] %x {"foo":10}'
    ].join('\n'));
  });


  QUnit.test("Recorder: level", function(assert) {
    var recorder = this.recorder, date = this.date.toISOString();
    recorder.addRecord(['foo'], {level: Logger.DEBUG});
    recorder.addRecord(['foo'], {level: Logger.INFO});
    recorder.addRecord(['foo'], {level: Logger.WARN});
    recorder.addRecord(['foo'], {level: Logger.ERROR});

    assert.equal(recorder.getFormattedRecords(), [
      '[' + date + ' DEBUG] foo',
      '[' + date + ' INFO] foo',
      '[' + date + ' WARN] foo',
      '[' + date + ' ERROR] foo'
    ].join('\n'));
  });


  QUnit.test("Recorder: type", function(assert) {
    var recorder = this.recorder, date = this.date.toISOString();
    var dom = document.createElement('div');
    var big = (function() {
      var i = 0, data = {};
      while (++i < 100) {
        data['foo' + i] = i;
      }
      return data;
    })();
    var modelWithoutId = {
      _class: 'Foo_Bar'
    };
    var modelWithId = {
      _class: 'Foo_Bar_ID',
      _id: {id: 1}
    };

    recorder.addRecord([null, null, NaN, undefined, Infinity], {level: Logger.INFO});
    recorder.addRecord([0, -0, -1, 1.123, -1.123, 0x10], {level: Logger.INFO});
    recorder.addRecord([[], {}, [1, 2, 3], {foo: 123}, [{foo: 10}], /foo/, modelWithoutId, modelWithId, this.date, dom, big, Object.keys(big)], {level: Logger.INFO});

    assert.equal(recorder.getFormattedRecords(), [
      '[' + date + ' INFO] null null NaN undefined Infinity',
      '[' + date + ' INFO] 0 0 -1 1.123 -1.123 16',
      '[' + date + ' INFO] [] {} [1,2,3] {"foo":123} [{"foo":10}] /foo/ [Foo_Bar] [Foo_Bar_ID:1] ' + date + ' [object HTMLDivElement] {"foo1":1,"foo2":2,"foo3":3,"foo4":4,"foo5":5,…} ["foo1","foo2","foo3","foo4","foo5","foo6","fo…]'
    ].join('\n'));
  });

  QUnit.test("Recorder: recordMaxSize", function(assert) {
    var records = null;
    var recorder = new Recorder({
      recordMaxSize: 2
    });
    
    recorder.addRecord(['foo1'], {level: Logger.INFO});

    records = recorder.getRecords();
    assert.equal(1, records.length);
    assert.deepEqual(['foo1'], records[0].messages);

    recorder.addRecord(['foo2'], {level: Logger.INFO});

    records = recorder.getRecords();
    assert.equal(2, records.length);
    assert.deepEqual(['foo1'], records[0].messages);
    assert.deepEqual(['foo2'], records[1].messages);

    recorder.addRecord(['foo3'], {level: Logger.INFO});
    records = recorder.getRecords();
    assert.equal(2, records.length);
    assert.deepEqual(['foo2'], records[0].messages);
    assert.deepEqual(['foo3'], records[1].messages);
  });
});
