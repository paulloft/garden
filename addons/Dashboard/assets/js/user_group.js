var GroupChecker = function(table) {

    var hover = table.data('hover');

    var toggle = function(checkboxes){
        var checked = checkboxes.first().is(':checked');
        if (checked) {
            checkboxes.prop('checked', false);
        } else {
            checkboxes.prop('checked', true);
        }
    };

    var getSelector = function(object, target, parent) {
        var index = $(object).index() + 1;
        if(target) {
            selector = target.replace(/\$index/g, index);
            selector = parent ? $(object).parent(parent).find(selector) : table.find(selector);
            selector = selector.add($(object));
        } else {
            selector = $(object);
        }
        return selector;
    }

    var click = function(select, target, parent) {
        $('.gc'+select, table).click(function() {
            var selector = getSelector(this, target, parent);
            toggle(selector.find('input[type="checkbox"]'));
        });
    }

    var mouseenter = function(select, target, parent) {
        $('.gc'+select, table).mouseenter(function() {
            var selector = getSelector(this, target, parent);
            selector.addClass(hover);
        });
    }

    var mouseleave = function(select, target, parent) {
        $('.gc'+select, table).mouseleave(function() {
            var selector = getSelector(this, target, parent);
            selector.removeClass(hover);
        });
    }

    var initEvents = function(select, target, parent) {
        click(select, target, parent);
        if(hover) {
            mouseenter(select, target, parent);
            mouseleave(select, target, parent);
        }
    }

    var init = function() {
        initEvents('.gc-check');
        initEvents('.gc-row', '.gc-check', 'tr');
        initEvents('.gc-column', 'td.gc-check:nth-child($index)');
        initEvents('.gc-all', '.gc-check');
    }
    
    init();
}

$(function(){
    $('.checkboxed-table').each(function(){
        GroupChecker($(this));
    });
});