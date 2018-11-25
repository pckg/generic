<template>
    <div class="pckg-htmlbuilder-dropzone" :id="id">
        <div class="row">
            <div class="preview" :class="[ current ? 'col-md-3' : '' ]">
                <img :src="cdn(current)" class="img-responsive"/>
            </div>
            <div :class="[ current ? 'col-md-9' : 'col-md-12' ]">
                <p>
                    <button type="button" class="btn btn-info select-files">Select</button>
                    or drop file for upload.
                </p>
                <p v-if="original && current != original">
                    <button type="button" class="btn btn-warning">Restore</button>
                    original file.
                </p>
                <p v-if="current">
                    <button type="button" class="btn btn-danger" @click.prevent="deleteFile">Delete</button>
                    current file.
                </p>

                <div class="table table-striped files" id="previews"></div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mixins: [pckgCdn],
        props: {
            current: {
                type: String,
                default: ''
            },
            prev: {
                type: String,
                default: ''
            },
            url: {
                type: String,
                default: ''
            },
            params: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            id: {
                type: String,
                default: ''
            },
            value: {
                type: String,
                default: ''
            }
        },
        data: function () {
            return {
                original: null,
                _dropzone: null,
                _previewTemplate: null
            };
        },
        watch: {
            url: function (n, o) {
                this.initDropzone();
            },
            params: function (n, o) {
                this.initDropzone();
            }
        },
        methods: {
            initDropzone: function () {
                if (!this.url) {
                    console.log("no upload url");
                    return;
                }

                if (this._dropzone) {
                    console.log('destroying dropzone');
                    this._dropzone.destroy();
                }

                console.log('creating dropzone ' + '#' + this.id);

                if (!this._previewTemplate) {
                    /*var previewNode = document.querySelector("#template");
                     previewNode.id = "";
                     previewNode.parentNode.removeChild(previewNode);
                     this._previewTemplate = previewNode.parentNode.innerHTML;*/
                    this._previewTemplate = '<div>' +
                        '<p class="size" data-dz-size></p>' +
                        '<div class="progress progress-striped active" role="progressbar" aria-valuemin="0"' +
                        ' aria-valuemax="100" aria-valuenow="0">' +
                        '<div class="progress-bar progress-bar-success" style="width:0%;"' +
                        ' data-dz-uploadprogress></div>' +
                        '</div>' +
                        '</div>';
                }


                this.original = this.current;
                this._dropzone = new Dropzone('#' + this.id, {
                    url: this.url,
                    params: this.params,
                    previewsContainer: '#previews',
                    previewTemplate: this._previewTemplate,
                    clickable: $(this.$el).parent().find('.select-files').get()[0],
                    maxFilesize: 8,
                    success: function (file, data) {
                        if (data.success) {
                            this.prev = this.current;
                            this.current = data.url;
                        }

                        if (data.message) {
                            $dispatcher.$emit('notification:' + (data.success ? 'success' : 'error'), data.message);
                        }

                        this.$emit('input', data.url);
                    }.bind(this)
                });
            },
            deleteFile: function () {
                http.deleteJSON(this.url, function () {
                    this.current = '';
                }.bind(this));
            }
        },
        created: function () {
            this.$nextTick(function () {
                this.initDropzone();
            }.bind(this));
        }
    }
</script>