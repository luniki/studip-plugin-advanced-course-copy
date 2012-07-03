now = -> new Date().toString("yyyy-MM-ddTHH:mm:ss")

# TODO docs
window.TodoList = Backbone.RelationalModel.extend

  relations: [
    type: Backbone.HasMany
    key: 'tasks'
    relatedModel: 'Task'
    collectionType: 'TaskCollection'
    reverseRelation:
      key: 'list'
      includeInJSON: 'id'
  ]

  sync: Backbone.trails_sync

  url: (action) ->
    "#{STUDIP.ACC.base_url}/lists/#{action}/#{@id}.json"

# TODO docs
window.Task = Backbone.RelationalModel.extend

  sync: Backbone.trails_sync

  url: (action) ->
    "#{STUDIP.ACC.base_url}/lists/#{@get 'todo_list_id'}/tasks/#{action}/#{@id}.json"

  # Toggle the `done` state of this todo item.
  toggleCompleted: ->
    attributes = completed_at: if @get "completed_at" then null else now()
    @save attributes


# TODO docs
window.TaskCollection = Backbone.Collection.extend
  model: Task

  # Filter down the list to only todo items that are still not finished.
  remaining: ->
    @without.apply @, @done()

  # Filter down the list of all todo items that are finished.
  done: ->
    @filter (todo) -> todo.get 'completed_at'

# TODO docs
class TaskView extends Backbone.View

  tagName:  "li"

  # Cache the template function for a single item.
  template: _.template $('#item-template').html()

  # The DOM events specific to an item.
  events:
    "click .check"               : "toggleDone"
    "click .todo-switch-content" : "toggleContent"

  # The TaskView listens for changes to its model, re-rendering. Since there's
  # a one-to-one correspondence between a **Task** and a **TaskView** in this
  # app, we set a direct reference on the model for convenience
  initialize: () ->
    _.bindAll @, 'render', 'remove'
    @model.on 'change', @render
    @model.on 'destroy', @remove

  # Re-render the contents of the todo item.
  render: () ->
    $(@el).html @template @model.toJSON()
    @

  # Toggle the `"done"` state of the model.
  toggleDone: () ->
    el = @$el.addClass "saving"
    @model.toggleCompleted?().done ->
      el.removeClass "saving"


  toggleContent: () ->
    @$el.toggleClass "todo-show-long-content"


# TODO docs
class TodoListView extends Backbone.View

  el: $ "#todoapp"

  events:
    "click button.delete-todo-list": "deleteList"

  initialize: (options) ->

    @list = options.list
    @list.on 'add:tasks',     @addOne
    @list.on 'reset:tasks',   @addAll
    @list.get('tasks').on 'change', @checkRemaining

  render: ->
    @

  addOne: (model, collection) =>
    view = new TaskView model: model
    @.$("#todo-list").append view.render().el

  addAll: (tasks_collection) =>
    _.each tasks_collection.models, @addOne
    @checkRemaining _.first tasks_collection.models

  checkRemaining: (task) =>
    done = task.collection?.remaining().length is 0
    if done
      @$('button.delete-todo-list').attr "disabled", false
    else
      @$('button.delete-todo-list').attr "disabled", "disabled"


  deleteList: (event) =>
    @$("button").attr("disabled", "disabled").wrapInner("<span/>").children()#.showAjaxNotification()
    ###
    xhr = @list.destroy()
    xhr?.done ->
      console.log "TODO:ERROR" unless match = window.location.search.match(/cid=(\w+)/)
      window.location.href = STUDIP.ABSOLUTE_URI_STUDIP + "seminar_main.php?cid=" + match[1]
    ###
    off

todo_list = new TodoList STUDIP.ACC.initial_todo_list
todo_list_view = new TodoListView list: todo_list

todo_list.get('tasks').reset STUDIP.ACC.initial_tasks
