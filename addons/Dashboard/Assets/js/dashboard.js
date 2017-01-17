var App = function() {
    var body, wrapper, sidebar, content;

    var init = function() {
        body = jQuery('body');
        wrapper = jQuery('.main-wrapper');
        sidebar = jQuery('.sidebar');
        content = jQuery('#content');

        // Initialize Tabs
        body.on('click', '[data-toggle="tabs"] a, .js-tabs a', function(e){
            e.preventDefault();
            var t = jQuery(this),
                href = t.attr('href'),
                title = t.text();

            window.history.pushState({}, title, href);
            
            jQuery(this).tab('show');
        });
    }

    var sidebarTimer;
    var toggleMenu = function(mode){
        if( wrapper.hasClass('menu-opened') && mode != 'open') {
            sidebar.addClass('delay');
            wrapper.removeClass('menu-opened');
            sidebarTimer = setTimeout(function(){wrapper.removeClass('no-overflow')}, 250);
        } else if(mode != 'close') {
            clearTimeout(sidebarTimer);
            sidebar.removeClass('delay');
            wrapper.addClass('menu-opened no-overflow');
        }
    }

    // Main navigation functionality
    var uiNav = function() {
        jQuery('[data-toggle="nav-submenu"]').on('click', function(e){
            // Stop default behaviour
            e.stopPropagation();
            e.preventDefault();

            var link = jQuery(this);
            var parentLi = link.parent('li');

            if (parentLi.hasClass('open')) { 
                parentLi.removeClass('open');
            } else { 
                link.closest('ul').find('> li').removeClass('open');
                parentLi.addClass('open');
            }
        });
    };

    var layout = function(mode) {
        switch(mode) {
            case 'menu_toggle':
                toggleMenu();
                break;

            case 'menu_open':
                toggleMenu('open');
                break;

            case 'menu_close':
                toggleMenu('close');
                break;

            case 'confirm':
                return confirm('Confirm your action');
                break;

            default: 
                return true;
        }

        return false;
    }

    var initEvents = function() {        
        body.on('click', 'a[href="#"]', function(e){
            e.preventDefault();
        });

        body.on('dblclick', function(e) {
            e.preventDefault();
        });

        body.on('click', '[data-action]', function(e){
            var mode = jQuery(this).attr('data-action');

            if (!layout(mode)) {
                e.preventDefault();
            }
        });
    }

    return {
        init: function() {
            init();
            uiNav();
            initEvents();
        },
        layout: function(mode) {
            return layout(mode);
        }
    }
}();

jQuery(function(){ App.init(); });