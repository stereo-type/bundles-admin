import $ from "jquery";
import jstree from "jstree";
import "jstree/dist/themes/default/style.css";
import "jstree/dist/themes/default-dark/style.css";
import './../css/tree.css';

const rootId = 'capability-tree';
function initializeJsTree(rootId) {
  const rootEl = document.getElementById(rootId);
  if (rootEl) {
    const itemId = rootEl.getAttribute('data-item-id') ?? null;
    const itemName = rootEl.getAttribute('data-item-name') ?? null;
    const dataSource = rootEl.getAttribute('data-source');
    const hiddenInput = document.querySelector("#" + rootEl.dataset.target);
    let url = `${dataSource}`;
    if (itemName) {
      url += `?${itemName}=${itemId}`;
    }

    $(`#${rootId}`).jstree({
      "core": {
        "data": {
          "url": url,
          "dataType": "json",
          'data': function (node) {
            return node;
          }
        },
        "check_callback": true,
        "themes": {
          "variant": "large",
          "icons": false
        }
      },
      "plugins": ["wholerow", "checkbox", "dnd", "search", "sort"],
      "checkbox": {
        "keep_selected_style": false
      },
    });

    $.event.special.touchstart = {
      setup: function (_, ns, handle) {
        if (ns.includes("noPreventDefault")) {
          this.addEventListener("touchstart", handle, {passive: false});
        } else {
          this.addEventListener("touchstart", handle, {passive: true});
        }
      }
    };

    $(`#${rootId}`).on('changed.jstree', function (e, data) {
      const tree = $(this).jstree(true);
      const selectedNodeIds = tree.get_selected();
      hiddenInput.value = JSON.stringify(selectedNodeIds);
    });
  }

}

function initialize() {
  if (document.readyState === 'loading') {
    // Если документ все еще загружается, добавляем обработчик
    document.addEventListener('DOMContentLoaded', function () {
      initializeJsTree(rootId);
    });
  } else {
    // Если документ уже загружен, вызываем функцию сразу
    initializeJsTree(rootId);
  }
}

initialize();
