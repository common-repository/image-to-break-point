jQuery().ready(function($)
{
	Response.create([
	{ 
	    prop: "width" // property to base tests on
      , breakpoints: [0, 318, 480, 640, 960, 1024, 1200]
	},
	{
	    prop: "device-pixel-ratio" // property to base tests on
	  , breakpoints: [0, 1, 1.5, 2]
	  , lazy: true // enable lazyloading
	}]);
});
	