<template>
    <div class="pckg-htmleditor">
        <textarea class="form-control" v-model="value" :id="id"></textarea>
    </div>
</template>

<script>
    export default {
        name: 'pckg-htmleditor',
        props: {
            value: {
                default: '',
                type: String
            },
            id: {
                default: 'pckg-htmleditor',
                type: String
            },
            forcedRootBlock: {
                default: false
            }
        },
        watch: {
            value: function (n, o) {
                if (n != o) {
                    this.$nextTick(this.updateEditorValue.bind(this));
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
                this._editor = initTinymce(this.id, {
                    forced_root_block: this.forcedRootBlock,
                    setup: function (editor) {
                        editor.on('Change', function (e) {
                            this.emitChange(this._editor.getContent());
                        }.bind(this)).on('KeyDown', function (e) {
                            this.emitChange(this._editor.getContent());
                        }.bind(this));
                    }.bind(this)
                });
            }
        },
        created: function () {
            this.$nextTick(function () {
                this.initEditor();
            }.bind(this));
        },
        beforeDestroyed: function () {
            destroyTinymce(this.id);
        }
    }
</script>