flotSeries: null,
flotOptions: null,

ready: function() {
	 $.plot(this.$(), this.flotSeries, this.flotOptions);
}