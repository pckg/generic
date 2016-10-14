var pckgTabelizeFieldEditor = Vue.component('pckg-tabelize-field-editor', {
    template: '#pckg-tabelize-field-editor',
    data: function () {
        return {};
    },
    props: {
        value: null
    },
    computed: {
        strippedContent: function () {
            var tmp = document.createElement("div");
            tmp.innerHTML = this.value;

            return utils.nl2br((tmp.textContent || tmp.innerText || "").substr(0, 200));
        }
    }
});