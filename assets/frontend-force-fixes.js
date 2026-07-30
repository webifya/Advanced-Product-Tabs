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

    function fixElement(element) {
        setImportant(element, 'max-width', 'none');
        setImportant(element, 'height', 'auto');
        setImportant(element, 'margin-left', '0');
        setImportant(element, 'margin-right', '0');
        setImportant(element, 'position', 'static');
        setImportant(element, 'left', 'auto');
        setImportant(element, 'right', 'auto');
        setImportant(element, 'transform', 'none');
        setImportant(element, 'float', 'none');
        setImportant(element, 'text-align', 'left');
    }

    function fixWrapper(wrapper) {
        setImportant(wrapper, 'display', 'block');
        setImportant(wrapper, 'box-sizing', 'border-box');
        setImportant(wrapper, 'width', '100%');
        setImportant(wrapper, 'max-width', 'none');
        setImportant(wrapper, 'height', 'auto');
        setImportant(wrapper, 'margin', '0');
        setImportant(wrapper, 'padding-left', '0');
        setImportant(wrapper, 'padding-right', '0');
        setImportant(wrapper, 'position', 'static');
        setImportant(wrapper, 'left', 'auto');
        setImportant(wrapper, 'right', 'auto');
        setImportant(wrapper, 'transform', 'none');
        setImportant(wrapper, 'float', 'none');
        setImportant(wrapper, 'text-align', 'left');

        Array.prototype.forEach.call(wrapper.children, fixElement);
        wrapper.querySelectorAll('.section_wrapper, .container').forEach(fixElement);
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
