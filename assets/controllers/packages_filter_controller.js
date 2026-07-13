import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["tab", "content", "author", "package", "row"];

    connect() {
        this.activeTabValue = this.tabTargets[0].dataset.tab;
        this.updateTabs();
    }

    switchTab(event) {
        this.activeTabValue = event.currentTarget.dataset.tab;
        this.updateTabs();
    }

    updateTabs() {
        this.tabTargets.forEach(tab => {
            if (tab.dataset.tab === this.activeTabValue) {
                tab.classList.add("border-emerald-500", "text-emerald-600");
                tab.classList.remove("border-transparent", "text-slate-500");
            } else {
                tab.classList.remove("border-emerald-500", "text-emerald-600");
                tab.classList.add("border-transparent", "text-slate-500");
            }
        });

        this.contentTargets.forEach(content => {
            content.classList.toggle("hidden", content.dataset.tab !== this.activeTabValue);
        });
    }

    filter() {
        const authorFilter = this.authorTarget.value.toLowerCase();
        const packageFilter = this.packageTarget.value.toLowerCase();

        this.rowTargets.forEach(row => {
            const author = row.dataset.author.toLowerCase();
            const pkg = row.dataset.package.toLowerCase();

            const matchesAuthor = !authorFilter || author === authorFilter;
            const matchesPackage = !packageFilter || pkg === packageFilter;

            row.classList.toggle("hidden", !(matchesAuthor && matchesPackage));
        });

        // Hide sections if no rows visible? Might be complex with current structure.
        // For now just filtering rows is a good start.
    }
}
