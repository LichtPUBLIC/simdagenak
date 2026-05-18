/**
 * Extend jsGrid plugins
 * contributor ebta.setiawan@gmail.com
 */
$(document).ready(function() {
    MyApp.el = {};
    // Element icon font clickable yg digunakan di jsgrid customField
    MyApp.el.itemEdit = '<span class="fa fa-list-alt item-edit action-field" title="Edit data"></span>';
    MyApp.el.itemSave = '<span class="fa fa-save item-save action-field" title="Simpan data"></span>';
    MyApp.el.itemUndo = '<span class="fa fa-undo item-undo action-field" title="Cancel edit"></span>';
    MyApp.el.itemDelete = '<span class="fa fa-remove item-delete action-field" title="Hapus data"></span>';
    MyApp.el.itemSaveInsert = '<span class="fa fa-save item-save-insert action-field" title="Simpan data"></span>';
    MyApp.el.itemUndoInsert = '<span class="fa fa-undo item-undo-insert action-field" title="Cancel Insert"></span>';

    // customField untuk jsGrid
    var ActionField = function(config) {
        jsGrid.Field.call(this, config);
    };

    ActionField.prototype = new jsGrid.Field({
        width: 30,
        css: "action-field-container", // redefine general property 'css'
        align: "center", // redefine general property 'align'
        filtering: false,
        inserting: false,
        editing: false,
        buttonClass: "jsgrid-button",
        modeButtonClass: "jsgrid-mode-button",

        modeOnButtonClass: "jsgrid-mode-on-button",
        searchModeButtonClass: "jsgrid-search-mode-button",
        insertModeButtonClass: "jsgrid-insert-mode-button",

        insertButtonClass: "jsgrid-insert-button",

        searchModeButtonTooltip: "Switch to searching",
        insertModeButtonTooltip: "Switch to inserting",

        insertButtonTooltip: "Insert",

        editButton: true,
        deleteButton: true,
        clearFilterButton: true,
        modeSwitchButton: true,

        myCustomProperty: "foo", // custom property
        sorting: false,
        sorter: $.noop,
        _initConfig: function() {
            this._hasFiltering = this._grid.filtering;
            this._hasInserting = this._grid.inserting;

            if(this._hasInserting && this.modeSwitchButton) {
                this._grid.inserting = false;
            }

            this._configInitialized = true;
        },
        headerTemplate: function() {
            if (!this._configInitialized) {
                this._initConfig();
            }

            var hasFiltering = this._hasFiltering;
            var hasInserting = this._hasInserting;

            if (!this.modeSwitchButton || (!hasFiltering && !hasInserting))
                return "";

            if (hasFiltering && !hasInserting)
                return this._createFilterSwitchButton();

            if (hasInserting && !hasFiltering)
                return this._createInsertSwitchButton();

            return this._createModeSwitchButton();
        },
        itemTemplate: function(value) {
            return MyApp.el.itemEdit + MyApp.el.itemDelete;
        },

        insertTemplate: function(value) {
            // return this._createInsertButton();
            return MyApp.el.itemSaveInsert + MyApp.el.itemUndoInsert;
        },

        editTemplate: function(value) {
            return MyApp.el.itemSave + MyApp.el.itemUndo;
        },

        _createInsertSwitchButton: function() {
            return this._createOnOffSwitchButton("inserting", this.insertModeButtonClass, false);
        },
        _createModeSwitchButton: function() {
            var isInserting = false;

            var updateButtonState = $.proxy(function() {
                $button.attr("title", isInserting ? this.searchModeButtonTooltip : this.insertModeButtonTooltip)
                    .toggleClass(this.insertModeButtonClass, !isInserting)
                    .toggleClass(this.searchModeButtonClass, isInserting);
            }, this);

            var $button = this._createGridButton(this.modeButtonClass, "", function(grid) {
                isInserting = !isInserting;
                grid.option("inserting", isInserting);
                grid.option("filtering", !isInserting);
                updateButtonState();
            });

            updateButtonState();

            return $button;
        },
        _createInsertButton: function() {
            return this._createGridButton(this.insertButtonClass, this.insertButtonTooltip, function(grid) {
                grid.insertItem().done(function() {
                    grid.clearInsert();
                });
            });
        },
        _createOnOffSwitchButton: function(option, cssClass, isOnInitially) {
            var isOn = isOnInitially;

            var updateButtonState = $.proxy(function() {
                $button.toggleClass(this.modeOnButtonClass, isOn);
            }, this);

            var $button = this._createGridButton(this.modeButtonClass + " " + cssClass, "", function(grid) {
                isOn = !isOn;
                grid.option(option, isOn);
                updateButtonState();
            });

            updateButtonState();

            return $button;
        },
        _createGridButton: function(cls, tooltip, clickHandler) {
            var grid = this._grid;

            return $("<input>").addClass(this.buttonClass)
                .addClass(cls)
                .attr({
                    type: "button",
                    title: tooltip
                })
                .on("click", function(e) {
                    clickHandler(grid, e);
                });
        },
    });



    jsGrid.fields.action = ActionField;

    // Aktivasi event ActionField jsGrid
    jsGrid.setActionFieldEvent = function(jsGridSelector) {
        var curJsGrid = MyApp.$me(jsGridSelector);
        curJsGrid.on('click', '.item-edit', function(e) {
            curJsGrid.jsGrid("editItem", $(this).closest('tr'));
        });

        curJsGrid.on('click', '.item-delete', function(e) {
            curJsGrid.jsGrid("deleteItem", $(this).closest('tr'));
        });

        curJsGrid.on('click', '.item-undo', function(e) {
            curJsGrid.jsGrid("cancelEdit");
        });

        curJsGrid.on('click', '.item-save', function(e) {
            curJsGrid.jsGrid("updateItem");
        });

        curJsGrid.on('click', '.item-undo-insert', function() {
            var btn = $(this).parents('.jsgrid').find('.jsgrid-insert-mode-button');
            if(btn.length > 0) btn.removeClass('jsgrid-mode-on-button');
            $(this).closest('tr').hide();
        });

        curJsGrid.on('click', '.item-save-insert', function(e) {
            curJsGrid.jsGrid("insertItem");
        });
    }
});

//# sourceURL=app/extend.jsGrid.js
