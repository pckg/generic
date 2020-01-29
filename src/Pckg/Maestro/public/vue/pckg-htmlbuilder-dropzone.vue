<template>

    <div class="c-pckg-htmlbuilder-dropzone" :class="stateClass" :id="id">

        <!-- visible icon -->
        <div class="as-table" v-if="iconClass" :style="{minHeight: minHeight}">
            <div class="s-icon text-center">
                <i class="__state-icon fa-fw" :class="iconClass"></i>
            </div>
            <div class="s-text">
                <span class="__percentage-progress" v-if="state == 'uploading' && progress > 0">{{ progress }}%</span>
                {{ infoText }}
            </div>
            <div class="s-action text-right" v-if="!state || ['uploading', 'drag', 'success'].indexOf(state) == -1">
                <a href="#" @click.prevent="openSelection">Upload new</a>
            </div>
        </div>

        <!-- visible image -->
        <div class="as-table" v-else-if="myCurrent" :style="{minHeight: minHeight}">
            <div class="s-img" v-if="!iconClass">
                <a :href="cdn(myCurrent)" class="__img-link" v-popup-image>
                    <img :src="cdn(myCurrent)" class="__img"/>
                </a>
            </div>
            <div class="s-text">
                {{ infoText }}
            </div>
            <div class="s-action text-left">
                <a href="#" @click.prevent="openSelection">Re-upload</a> <br/>
                <a href="#" @click.prevent="deleteFile">Delete</a>
            </div>
        </div>

        <!-- dropzone legacy -->
        <div type="file" class="hidden" ref="upload"/>
        <div :id="id + '-previews'" class="hidden"></div>

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
            },
            options: {
                default: function () {
                    return {
                        maxSize: 2
                    };
                }
            }
        },
        data: function () {
            return {
                original: null,
                _dropzone: null,
                _previewTemplate: null,
                myCurrent: this.current,
                state: null,
                hover: false,
                progress: 0,
                myOptions: this.options,
                minHeight: 'auto'
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
        computed: {
            stateClass: function () {
                if (this.state === 'error') {
                    return '--error';
                }

                if (this.state === 'success') {
                    return '--success';
                }

                if (this.state === 'drag' && this.hover) {
                    return 'is-hover';
                }
            },
            iconClass: function () {
                if (!this.state) {
                    if (this.myCurrent) {
                        return null;
                    }

                    return 'fal fa-image';
                }

                if (this.state == 'drag') {
                    return 'fal fa-arrow-up';
                }

                if (this.state == 'uploading') {
                    return 'fal fa-spinner-third fa-spin';
                }

                if (this.state == 'success') {
                    return 'fal fa-check';
                }

                if (this.state == 'error') {
                    return 'fal fa-exclamation-triangle';
                }
            },
            infoText: function () {
                if (this.state == 'uploading') {
                    return 'Uploading ...';
                }

                if (this.state == 'error') {
                    return this.errorMessage;
                }

                if (this.state == 'success') {
                    return 'File successfully uploaded';
                }

                if (this.state == 'drag') {
                    return 'Drop a file to upload';
                }

                if (this.myCurrent) {
                    let file = this.myCurrent.split('/').reverse()[0];
                    let short = file.substring(0, 7) + '...' + file.split('.').reverse()[0];

                    if (short.length < file.length) {
                        return short;
                    }

                    return file;
                }

                return 'Drop here to upload';
            }
        },
        methods: {
            initDropzone: function () {
                if (this._dropzone) {
                    this._dropzone.destroy();
                }

                if (!this.url) {
                    return;
                }

                this.original = this.myCurrent;
                this._dropzone = new Dropzone('#' + this.id, {
                    url: this.url,
                    params: this.params,
                    previewsContainer: '#' + this.id + '-previews',
                    previewTemplate: '<div></div>',
                    clickable: this.$refs.upload,
                    maxFilesize: this.options.maxSize,
                    acceptedFiles: this.accept,
                    uploadprogress: function (file, progress, bytesSent) {
                        this.state = 'uploading';
                        this.progress = progress;
                    }.bind(this),
                    success: function (file, data) {
                        this.hover = false;
                        this.state = 'success';
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

                        setTimeout(function () {
                            this.setNullState();
                        }.bind(this), 3333);
                    }.bind(this),
                    error: function (data, response) {
                        this.hover = false;
                        this.state = 'error';
                        this.errorMessage = 'Error uploading file';
                        this.$emit('uploaded', {
                            url: null,
                            data: data
                        });
                    }.bind(this),
                    dragenter: function (e) {
                        this.hover = true;
                        $dispatcher.$emit('pckg-htmlbuilder-dropzone:all:dragenter', this.id);
                    }.bind(this),
                    dragleave: function (e) {
                        if (!($(e.target).is($('#' + this.id)))) {
                            return;
                        }
                        this.hover = false;
                    }.bind(this),
                    init: function () {
                        this.on("drop", function (e) {
                            $dispatcher.$emit('body:dragend', e);
                            $('body').removeClass('has-drag');
                        });
                    }
                });
            },
            deleteFile: function () {
                let params = this.params;
                let esc = encodeURIComponent;
                let query = Object.keys(params)
                    .map(k => esc(k) + '=' + esc(params[k]))
                    .join('&');

                this.state = 'deleting';
                http.deleteJSON(this.url + (Object.keys(params).length > 0 ? '?' + query : ''), function () {
                    this.myCurrent = null;
                    this.state = null;
                    this.$emit('deleted');
                }.bind(this), function () {
                    this.state = 'error';
                    this.errorMessage = 'Error deleting file';
                }.bind(this));
            },
            openSelection: function () {
                this.errorMessage = null;
                this.state = null;
                this._dropzone.hiddenFileInput.click();
            },
            setDragState: function () {
                this.minHeight = $(this.$el).height() + 'px';
                this.state = 'drag';
            },
            setNullState: function () {
                this.state = null;
            },
            setNullStateWithHeight: function () {
                this.minHeight = 'auto';
                this.setNullState();
            },
            checkAllDragEnter: function (id) {
                if (this.id == id) {
                    return;
                }
                this.hover = false;
            }
        },
        mounted: function () {
            this.$nextTick(function () {
                this.initDropzone();
            }.bind(this));
        },
        created: function () {
            $dispatcher.$on('body:dragenter', this.setDragState);
            $dispatcher.$on('body:dragleave', this.setNullState);
            $dispatcher.$on('body:dragend', this.setNullStateWithHeight);
            $dispatcher.$on('pckg-htmlbuilder-dropzone:all:dragenter', this.checkAllDragEnter);
        },
        beforeDestroy: function () {
            $dispatcher.$off('body:dragenter', this.setDragState);
            $dispatcher.$off('body:dragleave', this.setNullState);
            $dispatcher.$off('body:dragend', this.setNullStateWithHeight);
            $dispatcher.$off('pckg-htmlbuilder-dropzone:all:dragenter', this.checkAllDragEnter);
        }
    }
</script>