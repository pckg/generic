var pckgTabelizeFieldEditor = Vue.component('pckg-htmlbuilder-dropzone', {
    template: '#pckg-htmlbuilder-dropzone',
    props: {
        current: null,
        url: null
    },
    data: function () {
        return {
            original: null,
            _dropzone: null
        };
    },
    created: function () {
        this.$nextTick(function () {
            if (!this.url) {
                console.log("no upload url");
                return;
            }
            var previewNode = document.querySelector("#template");
            previewNode.id = "";
            var previewTemplate = previewNode.parentNode.innerHTML;
            previewNode.parentNode.removeChild(previewNode);

            this.original = this.current;

            this._dropzone = $(this.$el).dropzone({
                url: this.url,
                previewsContainer: '#previews',
                previewTemplate: previewTemplate,
                clickable: $(this.$el).parent().find('.select-files').get()[0],
                maxFilesize: 8,
                success: function (file, data) {
                    data = JSON.parse(data);

                    if (data.success) {
                        this.prev = this.current;
                        this.current = data.url;
                    }
                }.bind(this)
            });
        });
    }
});