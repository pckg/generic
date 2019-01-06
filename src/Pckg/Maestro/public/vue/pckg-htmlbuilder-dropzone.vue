<template>
    <div class="pckg-htmlbuilder-dropzone" :id="id">
        <div v-if="myCurrent" class="display-block">
            <img :src="cdn(myCurrent)" class="img-responsive"/>
            <br/>
        </div>
        <div class="display-block">
            <button v-if="myCurrent" type="button" class="btn btn-default" @click.prevent="deleteFile">
                <i class="fa fa-trash"></i> Delete file
            </button>

            <button type="button" class="btn btn-default select-files">
                <i class="fa fa-upload"></i> Upload file
            </button>

            <button v-if="original && myCurrent != original" type="button" class="btn btn-default">
                <i class="fa fa-refresh"></i> Restore original
            </button>
        </div>
        <div class="table table-striped files" id="previews"></div>
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
            },
            accept: {
                default: null
            }
        },
        data: function () {
            return {
                original: null,
                _dropzone: null,
                _previewTemplate: null,
                myCurrent: this.current
            };
        },
        watch: {
            current: function (current) {
                this.myCurrent = current;
            },
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


                this.original = this.myCurrent;
                this._dropzone = new Dropzone('#' + this.id, {
                    url: this.url,
                    params: this.params,
                    previewsContainer: '#previews',
                    previewTemplate: this._previewTemplate,
                    clickable: $(this.$el).parent().find('.select-files').get()[0],
                    maxFilesize: 8,
                    acceptedFiles: this.accept,
                    success: function (file, data) {
                        if (data.success) {
                            this.prev = this.myCurrent;
                            this.myCurrent = data.url;
                        }

                        if (data.message) {
                            $dispatcher.$emit('notification:' + (data.success ? 'success' : 'error'), data.message);
                        }

                        this.$emit('input', data.url);
                        this.$emit('uploaded', {
                            url: data.url,
                            data: data
                        });
                    }.bind(this),
                    error: function (data, response) {
                        this.$emit('uploaded', {
                            url: null,
                            data: data
                        });
                    }.bind(this)
                });
            },
            deleteFile: function () {
                http.deleteJSON(this.url, function () {
                    this.myCurrent = '';
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