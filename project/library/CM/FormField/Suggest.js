ready: function() {
	var field = this;
	var prePopulate = this.$(".prePopulate");
	prePopulate = prePopulate.length ? JSON.parse(prePopulate.val()) : null;
	var $input = this.$('input[type="text"]');
	$input.tokenInput(
		function (query, handle_results) {
			field.ajax('getSuggestions', {'term':query, 'options':field.getOptions()}, {
				success: function(results) {
					handle_results(query, results);
				}
			});
		},{
		resultsFormatter: function(item){
			var output = "<p>" + item.name + "</p>";
			if (item.description) {
				output += "<small>" + item.description + "</small>";
			}
			if (item.img) {
				output = "<img src=\"" + item.img + "\" />" + output;
			}
			return "<li>" + output + "</li>";
		},
		tokenFormatter: function(item) {
			var output = "<p>" + item.name + "</p>";
			if (item.img) {
				output = "<img src=\"" + item.img + "\" />" + output;
			}					
			return "<li>" + output + "</li>";
		},
		onAdd: function(item) {
			field.onAdd(item);
			field.onChange($input.tokenInput("get"));
		},
		onDelete: function(item) {
			field.onDelete(item);
			field.onChange($input.tokenInput("get"));
		},
		animateDropdown: false,
		preventDuplicates: true,
		hintText: null,
		deleteText: '',
		searchDelay: 0,
		tokenLimit: field.getOption("cardinality"),
		prePopulate: prePopulate,
		classes: {
			tokenDelete: 'token-input-delete-token icon close small',
			focused: 'focus'
		}
	});
	this.getForm().$().bind("reset", function() {
		$input.tokenInput("clear");
	});
	if (this.getOption("cardinality") == 1) {
		this.$(".token-input-list").click(function() {
			$input.tokenInput("clear");
		});
	}
	this.onChange($input.tokenInput("get"));
},

onAdd: function(item) {
},

onDelete: function(item) {
},

onChange: function(items) {
}
