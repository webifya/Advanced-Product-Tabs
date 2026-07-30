(function () {
    'use strict';

    function setImportant(element, property, value) {
        if (element && element.style) {
            element.style.setProperty(property, value, 'important');
        }
    }

    function directChildren(list, tagName) {
        return Array.prototype.filter.call(list.children, function (child) {
            return child.tagName && child.tagName.toLowerCase() === tagName;
        });
    }

    function addMarker(item, text) {
        var marker = null;

        Array.prototype.some.call(item.children, function (child) {
            if (child.classList && child.classList.contains('wct-hard-marker')) {
                marker = child;
                return true;
            }
            return false;
        });

        if (!marker) {
            marker = document.createElement('span');
            marker.className = 'wct-hard-marker';
            marker.setAttribute('aria-hidden', 'true');
            item.insertBefore(marker, item.firstChild);
        }

        marker.textContent = text;
        setImportant(marker, 'display', 'inline-block');
        setImportant(marker, 'position', 'absolute');
        setImportant(marker, 'left', '0');
        setImportant(marker, 'top', '0');
        setImportant(marker, 'width', '1.35em');
        setImportant(marker, 'text-align', 'left');
        setImportant(marker, 'font', 'inherit');
        setImportant(marker, 'line-height', 'inherit');
        setImportant(marker, 'color', 'currentColor');
    }

    function fixList(list) {
        var ordered = list.tagName.toLowerCase() === 'ol';

        setImportant(list, 'display', 'block');
        setImportant(list, 'width', '100%');
        setImportant(list, 'max-width', 'none');
        setImportant(list, 'margin', '0 0 1em 0');
        setImportant(list, 'padding', '0');
        setImportant(list, 'list-style', 'none');
        setImportant(list, 'text-align', 'left');

        directChildren(list, 'li').forEach(function (item, index) {
            setImportant(item, 'display', 'block');
            setImportant(item, 'position', 'relative');
            setImportant(item, 'width', '100%');
            setImportant(item, 'max-width', 'none');
            setImportant(item, 'margin', '0 0 .45em 0');
            setImportant(item, 'padding', '0 0 0 1.55em');
            setImportant(item, 'float', 'none');
            setImportant(item, 'list-style', 'none');
            setImportant(item, 'text-align', 'left');
            addMarker(item, ordered ? String(index + 1) + '.' : '•');
        });
    }

    function fixBlock(element) {
        setImportant(element, 'box-sizing', 'border-box');
        setImportant(element, 'max-width', 'none');
        setImportant(element, 'margin-left', '0');
        setImportant(element, 'margin-right', '0');
        setImportant(element, 'left', 'auto');
        setImportant(element, 'right', 'auto');
        setImportant(element, 'transform', 'none');
        setImportant(element, 'float', 'none');
        setImportant(element, 'text-align', 'left');
    }

    function fixContainer(element) {
        fixBlock(element);
        setImportant(element, 'display', 'block');
        setImportant(element, 'width', '100%');
        setImportant(element, 'height', 'auto');
        setImportant(element, 'margin', '0');
        setImportant(element, 'padding-left', '0');
        setImportant(element, 'padding-right', '0');
        setImportant(element, 'position', 'static');
    }

    function fixWrapper(wrapper) {
        fixContainer(wrapper);
        setImportant(wrapper, 'align-self', 'stretch');
        setImportant(wrapper, 'flex', '1 1 100%');

        if (wrapper.parentElement) {
            setImportant(wrapper.parentElement, 'text-align', 'left');
            setImportant(wrapper.parentElement, 'align-items', 'stretch');
            setImportant(wrapper.parentElement, 'justify-content', 'flex-start');
        }

        wrapper.querySelectorAll('.section_wrapper, .container, .column, .column_attr, section, article').forEach(fixContainer);
        wrapper.querySelectorAll('p, h1, h2, h3, h4, h5, h6, div, blockquote, address, pre, figure, table').forEach(fixBlock);
        wrapper.querySelectorAll('ul, ol').forEach(fixList);
    }

    function run() {
        document.querySelectorAll('.wct-tab-content[data-wct-content="1"]').forEach(fixWrapper);
    }

    function scheduleRun() {
        window.requestAnimationFrame(function () {
            run();
            window.setTimeout(run, 50);
            window.setTimeout(run, 250);
            window.setTimeout(run, 1000);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scheduleRun);
    } else {
        scheduleRun();
    }

    window.addEventListener('load', scheduleRun);
    document.addEventListener('click', function () {
        window.setTimeout(scheduleRun, 20);
    });

    if ('MutationObserver' in window) {
        new MutationObserver(scheduleRun).observe(document.body || document.documentElement, {
            childList: true,
            subtree: true
        });
    }
}());
