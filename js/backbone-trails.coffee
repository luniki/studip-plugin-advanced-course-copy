actionMap =
  create: 'create'
  update: 'update'
  delete: 'destroy'
  read:   'show'

# Throw an error when a URL is needed, and none is supplied.
urlError = () ->
  throw new Error 'A "url" property or function must be specified'

Backbone.emulateHTTP = on

# thin wrapper around Backbone.sync
Backbone.trails_sync = (method, model, options) ->

  _.extend options,
    url: model.url?(actionMap[method]) ? do urlError

  Backbone.sync method, model, options
