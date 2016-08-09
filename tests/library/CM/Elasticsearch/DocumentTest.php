<?php

class CM_Elasticsearch_DocumentTest extends CMTest_TestCase {

    public function testConstructor() {
        $data = ['name' => 'John', 'age' => 54];
        $document = new CM_Elasticsearch_Document('1', $data);

        $this->assertInstanceOf('CM_Elasticsearch_Document', $document);
        $this->assertSame('1', $document->getId());
        $this->assertSame('John', $document->get('name'));
        $this->assertSame(54, $document->get('age'));
    }

    public function testSetters() {
        $data = ['name' => 'John', 'age' => 54];
        $document = new CM_Elasticsearch_Document('1', $data);

        $this->assertEquals($data, $document->getData());

        $document->set('sex', 'male');
        $this->assertSame('male', $document->get('sex'));

        $document->remove('sex');
        $this->assertFalse($document->has('sex'));

        $exception = $this->catchException(function () use ($document) {
            $document->get('someReallyWeirdKey');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Field does not exist', $exception->getMessage());
        $this->assertSame(['key' => 'someReallyWeirdKey'], $exception->getMetaInfo());

        $data2 = ['name' => 'Bill', 'height' => 178];
        $document->setData(['name' => 'Bill', 'height' => 178]);

        $this->assertEquals($data2, $document->getData());
        $this->assertSame('1', $document->getId());
    }

    public function testCreate() {
        $data = ['surname' => 'Doe', 'height' => 165];
        $document = CM_Elasticsearch_Document::create($data);
        $this->assertInstanceOf('CM_Elasticsearch_Document', $document);
        $this->assertNull($document->getId());
    }
}
