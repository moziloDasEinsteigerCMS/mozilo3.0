document.addEventListener("DOMContentLoaded", function () {
    function createMoFilterPlugin(config) {
        if (!config.search_item || !config.search_name) return;

        const container = document.querySelector(config.selector);
        if (!container) return;

        let rows = [];
        let searchRows = [];

        // Create search UI
        const searchField = document.createElement("div");
        searchField.className = "js-mofilterplugin card";

        const label = document.createElement("label");
        label.className = "mo-bold mo-padding-right mo-padding-left";
		  label.htmlFor = "filter"; 
        label.textContent = mozilo_lang["filter_text_" + config.filter_action] + " " + mozilo_lang["filter_text"];
        searchField.appendChild(label);

        const searchInput = document.createElement("input");
        searchInput.id = "filter";
        searchInput.className = "mo-plugin-input mb";
        searchInput.type = "search";
        searchInput.name = "filter";
        searchField.appendChild(searchInput);

        // Insert searchField above container
        container.parentNode.insertBefore(searchField, container);

        // No-match message
        const noMatchMessage = document.createElement("div");
        noMatchMessage.className = "mo-no-match-message mo-padding-top mo-color-error";
        noMatchMessage.style.display = "none";
        noMatchMessage.textContent = mozilo_lang["filter_no_match"];

        // Place message inside the container (not the search UI)
        container.appendChild(noMatchMessage);

        // Special catpage button
        let toggleBtn = null;
        if (config.filter_action === "catpage") {
            toggleBtn = document.createElement("input");
            toggleBtn.type = "button";
            toggleBtn.className = "js-filter-page-hide mo-checkbox-del mo-td-middle";
            toggleBtn.value = mozilo_lang["filter_button_all_hide"];
            searchField.appendChild(toggleBtn);

            toggleBtn.addEventListener("click", function (e) {
                e.preventDefault();
                const pages = document.querySelectorAll(".js-li-page");
                if (toggleBtn.classList.contains("js-filter-page-hide")) {
                    pages.forEach(el => el.style.display = "none");
                    toggleBtn.value = mozilo_lang["filter_button_all_show"];
                    toggleBtn.classList.remove("js-filter-page-hide");
                } else {
                    pages.forEach(el => el.style.display = "inherit");
                    toggleBtn.value = mozilo_lang["filter_button_all_hide"];
                    toggleBtn.classList.add("js-filter-page-hide");
                }
            });
        }

        // Populate row data
        function makeRows() {
            rows = Array.from(container.querySelectorAll(config.search_item));
            searchRows = rows.map(row =>
                (row.querySelector(config.search_name)?.textContent || "").toLowerCase()
            );
        }

        // Filtering logic
        function filterRows() {
            makeRows();

            let searchStr = searchInput.value.trim().toLowerCase();
            if (!searchStr) {
                rows.forEach(row => row.style.display = "inherit");
                noMatchMessage.style.display = "none";

                if (config.filter_action === "catpage") {
                    document.querySelectorAll(".js-move-me-cat").forEach(el => {
                        el.style.opacity = "1";
                        el.style.cursor = "grabbing";
                        el.classList.remove("js-deact-filter");
                    });
                    document.querySelectorAll(".js-new-ul .js-li-cat").forEach(el => {
                        el.style.display = "inherit";
                    });
                }
                return;
            }

            // Regex processing
            searchStr = searchStr
                .replace(/[\-\[\]{}()\*?.,\\\^$|#]/g, "\\$&") // escape special chars
                .replace(/[\s]*[+]/g, "|")
                .replace(/[\s]/g, "\\s");

            const regex = new RegExp(searchStr, "gi");

            let anyVisible = false;
            rows.forEach((row, i) => {
                const text = searchRows[i];
                const match = text.search(regex) !== -1;
                row.style.display = match ? "inherit" : "none";
                if (match) anyVisible = true;
            });

            // Show/hide "no results" message
            noMatchMessage.style.display = anyVisible ? "none" : "block";

            // Contextual actions
            if (config.filter_action === "catpage") {
                document.querySelectorAll(".js-move-me-cat").forEach(el => {
                    el.style.opacity = "0.3";
                    el.style.cursor = "default";
                    el.classList.add("js-deact-filter");
                });
                document.querySelectorAll(".js-new-ul .js-li-cat").forEach(el => {
                    el.style.display = "none";
                });
            }

            if (config.filter_action === "plugins") {
                document.querySelectorAll(".js-plugin-del:checked").forEach(el => {
                    el.checked = false;
                });
            }
        }

        // Input events
        searchInput.addEventListener("keydown", function (e) {
            if (e.key === "Enter") e.preventDefault();
            if (e.key === "Escape") {
                searchInput.value = "";
                filterRows();
            }
        });

        ["keyup", "click"].forEach(evt => {
            searchInput.addEventListener(evt, filterRows);
        });

        searchInput.addEventListener("focus", makeRows);
    }

    // Initialize per action_activ
    if (action_activ === "gallery") {
        createMoFilterPlugin({
            selector: ".js-gallery",
            search_item: ".js-file-dir",
            search_name: ".js-gallery-name",
            filter_action: "gallery"
        });
    }

    if (action_activ === "plugins") {
        createMoFilterPlugin({
            selector: ".js-plugins",
            search_item: ".js-plugin",
            search_name: ".js-plugin-name",
            filter_action: "plugins"
        });
    }

    if (action_activ === "files") {
        createMoFilterPlugin({
            selector: ".js-files",
            search_item: ".js-file-dir",
            search_name: ".js-gallery-name",
            filter_action: "files"
        });
    }

    if (action_activ === "catpage") {
        createMoFilterPlugin({
            selector: ".js-ul-cats",
            search_item: ".js-li-cat",
            search_name: ".js-cat-name",
            filter_action: "catpage"
        });
    }

    if (action_activ === "template") {
        createMoFilterPlugin({
            selector: ".js-templates",
            search_item: ".js-template",
            search_name: ".js-template-name",
            filter_action: "template"
        });
    }
});
