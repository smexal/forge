var forge = typeof forge != 'undefined' ? forge : {};

forge = (function(forge) {

  forge.fields = typeof forge.fields != 'undefined' ? forge.fields : {};

  var proxy = function(obj, fn) {
    return function() {
      fn.apply(obj, arguments);
    }
  };

  var Repeater = function(root_elem, callbacks) {
    this.root_elem = root_elem;

    this.max = root_elem.getAttribute('data-max') || -1;
    this.count = root_elem.getAttribute('data-count') || 0;
    this.repeater_title = root_elem.getAttribute('data-repeater-title') || false;
    this.repeater_field = root_elem.querySelector('input.repeater-input');
    this.template = root_elem.querySelector('.repeater-template');
    this.entry_container = root_elem.querySelector('.repeater-content');
    this.append_button = root_elem.querySelector('.repeater-add');

    this.repeater_entries = this.readEntries();
    this.registerHandlers();

    Repeater.list.push(this);
  };
  
  Repeater.list = [];

  var EVT_PREFIX = 'forge.fields.Repeater:';
  Repeater.EVT_ADDENTRY = EVT_PREFIX + 'addentry';

  Repeater.prototype = {

    registerHandlers : function() {
      var self = this;
      this.append_button.addEventListener('click', function() {
        self.appendNewEntry(self.createNewEntry());
      });

      for(var i = 0; i < this.repeater_entries.length; i++) {
        this.registerEntryHandlers(this.repeater_entries[i]);
      }
    },

    registerEntryHandlers : function(entry) {
      var self = this;

      entry.querySelector('.control-add').addEventListener('click', function(e) {
        e.preventDefault();
        self.addEntryBelow(entry, self.createNewEntry());
      });

      entry.querySelector('.control-remove').addEventListener('click', function(e) {
        e.preventDefault();
        self.removeEntry(entry);
      });

      entry.querySelector('.control-up').addEventListener('click', function(e) {
        e.preventDefault();
        self.moveEntryUp(entry);
      });

      entry.querySelector('.control-down').addEventListener('click', function(e) {
        e.preventDefault();
        self.moveEntryDown(entry);
      });
    },

    readEntries  : function() {
      return this.entry_container.querySelectorAll('.repeater-entry');
    },

    appendNewEntry : function(new_entry) {
      this.entry_container.appendChild(new_entry);
      this.reindexFields();
      this.trigger(Repeater.EVT_ADDENTRY, {entry: new_entry});
    },

    addEntryAbove : function(entry, new_entry) {
      this.entry_container.insertBefore(new_entry, entry);
      this.reindexFields();
      this.trigger(Repeater.EVT_ADDENTRY, {entry: new_entry});
    },  

    addEntryBelow : function(entry, new_entry) {
      if(!entry.nextElementSibling) {
        this.appendNewEntry(entry, new_entry)
      }
      this.entry_container.insertBefore(new_entry, entry.nextElementSibling);
      this.reindexFields();
      this.trigger(Repeater.EVT_ADDENTRY, {entry: new_entry});

    },

    removeEntry : function(entry) {
      this.entry_container.removeChild(entry);
      delete entry;
      this.reindexFields();
    },

    moveEntryUp : function(entry) {
      var prev_entry = entry.previousElementSibling;
      if(!prev_entry) {
        return;
      }
      this.swapEntries(entry, prev_entry);
    },

    moveEntryDown : function(entry) {
      var next_entry = entry.nextElementSibling;
      if(!next_entry) {
        return;
      }
      this.swapEntries(entry, next_entry);
    },

    swapEntries : function(entry_a, entry_b) {
      var after_a = entry_a.nextElementSibling;
      var after_b = entry_b.nextElementSibling;

      this.entry_container.insertBefore(entry_a, after_b);
      this.entry_container.insertBefore(entry_b, after_a);

      this.reindexFields();
    },

    reindexFields : function() {
      this.entries = this.readEntries();
      for(var i = 0; i < this.entries.length; i++) {
        this.entries[i].querySelector('.repeater-title').textContent = this.repeater_title + " - " + i;
        var field_wrappers = this.entries[i].querySelectorAll('.fieldset-entry-field');
        for(var k = 0; k < field_wrappers.length; k++) {
          var field_wrapper = field_wrappers[k];


          var key = field_wrapper.getAttribute('data-key');
          var prefix = field_wrapper.getAttribute('data-key-prefix');
          var suffix = field_wrapper.getAttribute('data-key-suffix');
          var field = field_wrapper.querySelector('[name="' + key + '"]');
          
          field.setAttribute('name', prefix + i + suffix);
          field_wrapper.setAttribute('data-key', prefix + i + suffix);
        }
      }
      this.repeater_field.value = this.entries.length;

    },

    createNewEntry : function() {
      var tpl_string, wrapper, li;

      debugger;
      tpl_string = this.template.innerText;
      tpl_string = decodeURIComponent(tpl_string);

      wrapper = document.createElement('div');
      wrapper.innerHTML= tpl_string;

      li = wrapper.querySelector("li");
      li.classList.remove('repeater-entry-template');
      li.classList.add('repeater-entry');
      li.querySelector('.repeater-title').textContent = this.repeater_title + " - %ITERATION%"

      this.registerEntryHandlers(li);

      return li;
    },

    trigger : function(name, data) {
      data = data || {};
      data.instance = this;
      this.root_elem.dispatchEvent(new CustomEvent(name, {detail: data}));
    }
  };

  forge.fields.Repeater = Repeater;

  return forge;
})(forge);