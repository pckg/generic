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
            <div class="s-img" v-if="preview && !iconClass && isImage(myCurrent)">
                <a :href="cdn(myCurrent)" class="__img-link" v-popup-image>
                    <img :src="cdn(myCurrent)" class="__img"/>
                </a>
            </div>
            <div v-else class="s-icon text-center">
                <i class="__state-icon fal fa-fw fa-file"></i>
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
    import Dropzone from "dropzone";

    export default {
        name: 'pckg-htmlbuilder-dropzone',
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
            },
            icon: {
                default: 'image'
            },
            preview: {
                default: true
            }
        },
        data: function () {
            return {
                original: null,
                _dropzone: null,
                _dropzoneHash: null,
                _makeResolve: null,
                _makeReject: null,
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

                    return 'fal fa-' + this.icon;
                }

                if (this.state == 'drag') {
                    return 'fal fa-arrow-up';
                }

                if (this.state == 'queued') {
                    return 'fal fa-upload';
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
                if (this.state == 'queued') {
                    return 'File selected for upload';
                }

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
            isImage: function(file) {
                if (!file) {
                    return false;
                }

                let extension = file.split('.').reverse()[0].toLowerCase();
                return ['png', 'gif', 'png', 'webp', 'jpg', 'jpeg'].indexOf(extension) >= 0;
            },
            process: function () {
                return new Promise((resolve, reject) => {
                    this._makeResolve = resolve;
                    this._makeReject = reject;
                    this._dropzone.processQueue();
                });
            },
            dropzoneHash: function(){
                return JSON.stringify({
                    url: this.url,
                    params: this.params,
                });
            },
            initDropzone: function () {
                let hash = this.dropzoneHash();
                if (this._dropzoneHash === hash) {
                    return;
                } else {
                    this._dropzoneHash = hash;
                    this.state = null;
                }

                if (this._dropzone) {
                    this._dropzone.destroy();
                }

                if (!this.url) {
                    return;
                }

                let elements = document.getElementsByName('pckgvdth');
                let headers = {};
                if (elements.length === 1) {
                    headers = {
                        'X-Pckg-CSRF': elements[0].getAttribute('content')
                    };
                }

                this.original = this.myCurrent;
                this._dropzone = new Dropzone('#' + this.id, {
                    url: this.url,
                    params: this.params,
                    headers: headers,
                    previewsContainer: '#' + this.id + '-previews',
                    previewTemplate: '<div></div>',
                    clickable: this.$refs.upload,
                    autoProcessQueue: !this.options.manualProcess,
                    maxFilesize: this.options.maxSize,
                    acceptedFiles: this.accept,
                    uploadprogress: function (file, progress, bytesSent) {
                        this.state = 'uploading';
                        this.progress = progress;
                    }.bind(this),
                    success: function (file, data) {
                        this.hover = false;
                        if (data.success) {
                            this.prev = this.myCurrent;
                            this.myCurrent = data.url;
                            this.state = 'success';
                            if (this._makeResolve) {
                                this._makeResolve();
                            }
                        } else {
                            this.state = 'error';
                            this.errorMessage = data.message || 'Error uploading file';
                            if (this._makeReject) {
                                this._makeReject();
                            }
                        }

                        if (data.message) {
                            $dispatcher.$emit('notification:' + (data.success ? 'success' : 'error'), data.message);
                        }

                        if (data.success) {
                            this.$emit('input', data.url);
                            this.$emit('uploaded', {
                                url: data.url,
                                filename: data.filename || null,
                                data: data
                            });
                        }

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
                    }
                });
                this._dropzone.on("drop", (e) => {
                    $dispatcher.$emit('body:dragend', e);
                    $('body').removeClass('has-drag');
                });
                this._dropzone.on("addedfile", () => {
                    this.state = 'queued';
                });
                this._dropzone.on("addedfiles", () => {
                    this.state = 'queued';
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