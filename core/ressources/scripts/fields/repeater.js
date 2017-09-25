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
    this.template = root_elem.querySelector('.repeater-template');
    this.entry_container = root_elem.querySelector('.repeater-content');
    this.append_button = root_elem.querySelector('.repeater-add');

    this.repeater_entries = this.readEntries();
    this.registerHandlers();
  };
  
  Repeater.EVT_ADDENTRY = 'addentry';

  Repeater.prototype = {
    
    PREFIX_EVT : 'forge.fields.Repeater:',
    

    registerHandlers : function() {
      var self = this;
      this.append_button.addEventListener('click', function() {
        self.appendNewEntry(self.createNewEntry());
      });

      for(var i = 0; i < this.repeater_entries.length; i++) {
        this.registerRepeaterEntryHandlers(this.repeater_entries[i]);
      }
    },

    registerEntryHandlers : function(entry) {
      var self = this;
      entry.querySelector('.control-add').on('click', function(e) {
        e.preventDefault();
        self.addEntryBelow(entry, self.createNewEntry());
      });

      entry.querySelector('.control-remove').on('click', function(e) {
        e.preventDefault();
        self.removeEntry(entry, self.createNewEntry());
      });

      entry.querySelector('.control-up').on('click', function(e) {
        e.preventDefault();
        self.moveEntryUp(entry);
      });

      entry.querySelector('.control-down').on('click', function(e) {
        e.preventDefault();
        self.moveEntryDown(entry);
      });
    },

    readEntries  : function() {
      return this.entry_container.querySelectorAll('.repeater-entry');
    },

    appendNewEntry : function(new_entry) {
      this.entry_container.appendChild(new_entry);
      
      this.trigger(Repeater.EVT_ADDENTRY, new_entry);
      this.reindexFields();
    },

    addEntryAbove : function(entry, new_entry) {
      this.entry_container.insertBefore(new_entry, entry);

      this.reindexFields();
    },  

    addEntryBelow : function(entry, new_entry) {
      this.entry_container.insertAfter(new_entry, entry.nextSibling);

      this.reindexFields();
    },

    removeEntry : function(entry) {
      this.entry_container.remove(entry);

      this.reindexFields();
    },

    moveEntryUp : function(entry) {
      var prev_entry = entry.nextSibling;

      this.swapEntries(entry, prev_entry);
      this.reindexFields();
    },

    moveEntryDown : function(entry) {
      var next_entry = entry.previousSibling;

      this.swapEntries(entry, next_entry);
      this.reindexFields();
    },

    swapEntries : function(entry_a, entry_b) {
      var prev_a = entry_a.nextSibling;
      var prev_b = entry_b.nextSibling;

      this.entry_container.insertBefore(prev_a, entry_b);
      this.entry_container.insertBefore(prev_b, entry_a);

      this.reindexFields();
    },

    reindexFields : function(entry, new_entry) {
      this.entries = this.readEntries();
      for(var i = 0; i < this.entries.length; i++) {
        var field_wrappers = this.entries[i].querySelector('.fieldset-entry-field');
        for(var k = 0; k < field_wrappers.length; k++) {
          var key = field.attr('data-key');
          var prefix = field.attr('key-prefix');
          var suffix = field.attr('key-suffix');
          var field = field_wrapper.querySelector('name', key);
          field.attr('name', prefix + i + suffix);
        }
      }
    },

    createNewEntry : function() {
      var rendered = this.template.cloneNode(true);
      rendered.classList.remove("repeater-template");

      var li = document.createElement('li');
      li.appendChild(rendered);
      return li;
    },

    trigger : function(name, data) {
      data = data || {};
      data.instance = this;
      
      this.root_elem.dispatchEvent(new CustomEvent(this.prefix + name, data));
    }
  };

  forge.fields.Repeater = Repeater;

  return forge;
})(forge);