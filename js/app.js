(function() {
  var TaskView, TodoListView, now, todo_list, todo_list_view,
    __hasProp = Object.prototype.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor; child.__super__ = parent.prototype; return child; },
    __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

  now = function() {
    return new Date().toString("yyyy-MM-ddTHH:mm:ss");
  };

  window.TodoList = Backbone.RelationalModel.extend({
    relations: [
      {
        type: Backbone.HasMany,
        key: 'tasks',
        relatedModel: 'Task',
        collectionType: 'TaskCollection',
        reverseRelation: {
          key: 'list',
          includeInJSON: 'id'
        }
      }
    ],
    sync: Backbone.trails_sync,
    url: function(action) {
      return "" + STUDIP.ACC.base_url + "/lists/" + action + "/" + this.id + ".json";
    }
  });

  window.Task = Backbone.RelationalModel.extend({
    sync: Backbone.trails_sync,
    url: function(action) {
      return "" + STUDIP.ACC.base_url + "/lists/" + (this.get('todo_list_id')) + "/tasks/" + action + "/" + this.id + ".json";
    },
    toggleCompleted: function() {
      var attributes;
      attributes = {
        completed_at: this.get("completed_at") ? null : now()
      };
      return this.save(attributes);
    }
  });

  window.TaskCollection = Backbone.Collection.extend({
    model: Task,
    remaining: function() {
      return this.without.apply(this, this.done());
    },
    done: function() {
      return this.filter(function(todo) {
        return todo.get('completed_at');
      });
    }
  });

  TaskView = (function(_super) {

    __extends(TaskView, _super);

    function TaskView() {
      TaskView.__super__.constructor.apply(this, arguments);
    }

    TaskView.prototype.tagName = "li";

    TaskView.prototype.template = _.template($('#item-template').html());

    TaskView.prototype.events = {
      "click .check": "toggleDone",
      "click .todo-switch-content": "toggleContent"
    };

    TaskView.prototype.initialize = function() {
      _.bindAll(this, 'render', 'remove');
      this.model.on('change', this.render);
      return this.model.on('destroy', this.remove);
    };

    TaskView.prototype.render = function() {
      $(this.el).html(this.template(this.model.toJSON()));
      return this;
    };

    TaskView.prototype.toggleDone = function() {
      var el, _base;
      el = this.$el.addClass("saving");
      return typeof (_base = this.model).toggleCompleted === "function" ? _base.toggleCompleted().done(function() {
        return el.removeClass("saving");
      }) : void 0;
    };

    TaskView.prototype.toggleContent = function() {
      return this.$el.toggleClass("todo-show-long-content");
    };

    return TaskView;

  })(Backbone.View);

  TodoListView = (function(_super) {

    __extends(TodoListView, _super);

    function TodoListView() {
      this.deleteList = __bind(this.deleteList, this);
      this.checkRemaining = __bind(this.checkRemaining, this);
      this.addAll = __bind(this.addAll, this);
      this.addOne = __bind(this.addOne, this);
      TodoListView.__super__.constructor.apply(this, arguments);
    }

    TodoListView.prototype.el = $("#todoapp");

    TodoListView.prototype.events = {
      "click button.delete-todo-list": "deleteList"
    };

    TodoListView.prototype.initialize = function(options) {
      this.list = options.list;
      this.list.on('add:tasks', this.addOne);
      this.list.on('reset:tasks', this.addAll);
      return this.list.get('tasks').on('change', this.checkRemaining);
    };

    TodoListView.prototype.render = function() {
      return this;
    };

    TodoListView.prototype.addOne = function(model, collection) {
      var view;
      view = new TaskView({
        model: model
      });
      return this.$("#todo-list").append(view.render().el);
    };

    TodoListView.prototype.addAll = function(tasks_collection) {
      _.each(tasks_collection.models, this.addOne);
      return this.checkRemaining(_.first(tasks_collection.models));
    };

    TodoListView.prototype.checkRemaining = function(task) {
      var done, _ref;
      done = ((_ref = task.collection) != null ? _ref.remaining().length : void 0) === 0;
      if (done) {
        return this.$('button.delete-todo-list').attr("disabled", false);
      } else {
        return this.$('button.delete-todo-list').attr("disabled", "disabled");
      }
    };

    TodoListView.prototype.deleteList = function(event) {
      this.$("button").attr("disabled", "disabled").wrapInner("<span/>").children();
      /*
          xhr = @list.destroy()
          xhr?.done ->
            console.log "TODO:ERROR" unless match = window.location.search.match(/cid=(\w+)/)
            window.location.href = STUDIP.ABSOLUTE_URI_STUDIP + "seminar_main.php?cid=" + match[1]
      */
      return false;
    };

    return TodoListView;

  })(Backbone.View);

  todo_list = new TodoList(STUDIP.ACC.initial_todo_list);

  todo_list_view = new TodoListView({
    list: todo_list
  });

  todo_list.get('tasks').reset(STUDIP.ACC.initial_tasks);

}).call(this);
