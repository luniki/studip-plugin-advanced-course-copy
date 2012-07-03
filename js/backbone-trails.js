(function() {
  var actionMap, urlError;

  actionMap = {
    create: 'create',
    update: 'update',
    "delete": 'destroy',
    read: 'show'
  };

  urlError = function() {
    throw new Error('A "url" property or function must be specified');
  };

  Backbone.emulateHTTP = true;

  Backbone.trails_sync = function(method, model, options) {
    var _ref;
    _.extend(options, {
      url: (_ref = typeof model.url === "function" ? model.url(actionMap[method]) : void 0) != null ? _ref : urlError()
    });
    return Backbone.sync(method, model, options);
  };

}).call(this);
