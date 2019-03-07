<template>
    <div class="pckg-htmleditor">
        <textarea class="form-control" v-model="value" :id="id"></textarea>
    </div>
</template>

<script>
    export default {
        name: 'pckg-htmleditor',
        props: {
            id: {
                default: function () {
                    return 'pckg-editor-htmleditor' + parseInt(Math.random() * 1000000) % 1000000;
                }
            },
            value: {
                default: '',
                type: String
            },
            forcedRootBlock: {
                default: false
            }
        },
        model: {
            prop: 'value'
        },
        watch: {
            value: function (n, o) {
                if (n != o) {
                    this.updateEditorValue();
                }
            }
        },
        data: function () {
            return {
                _editor: null
            };
        },
        methods: {
            emitChange: function (value) {
                this.$emit('input', value);
                this.$emit('change', value);
            },
            updateEditorValue: function () {
                if (this._editor && this._editor.getContent() != this.value) {
                    this._editor.setContent(this.value || '');
                }
            },
            initEditor: function () {
                initTinymce(this.id, {
                    forced_root_block: this.forcedRootBlock,
                    setup: function (editor) {
                        this._editor = editor;
                        editor.on('Change', function (e) {
                            this.emitChange(this._editor.getContent());
                        }.bind(this)).on('KeyDown', function (e) {
                            this.emitChange(this._editor.getContent());
                        }.bind(this)).on('KeyUp', function (e) {
                            this.emitChange(this._editor.getContent());
                        }.bind(this));
                    }.bind(this)
                });
            }
        },
        mounted: function () {
            this.initEditor();
        },
        destroyed: function () {
            destroyTinymce(this.id);
        }
    }
</script>