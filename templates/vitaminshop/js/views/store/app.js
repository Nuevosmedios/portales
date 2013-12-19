var app_router = new AppRouter;

//Configuration Backbone
Backbone.emulateHTTP = false;
Backbone.emulateJSON = false;

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();