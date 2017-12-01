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
        /*model: {
         prop: 'value',
         event: 'input'
         },*/
        watch: {
            value: function (n, o) {
                if (n != o) {
                    this.$nextTick(this.updateEditorValue.bind(this));
                }
            }
        },
        methods: {
            emitChange: function (value) {
                this.$emit('input', value);
                this.$emit('change', value);
            },
            updateEditorValue: function () {
                var editor = tinymce.get(this.id);
                if (editor) {
                    var currentContent = editor.getContent();
                    if (this.value != currentContent) {
                        editor.setContent(this.value || '');
                    }
                }
            },
            initEditor: function () {
                var editor = initTinymce(this.id, function (editor) {
                    editor.on('Change', function (e) {
                        this.emitChange(tinymce.get(this.id).getContent());
                    }.bind(this)).on('KeyDown', function (e) {
                        this.emitChange(tinymce.get(this.id).getContent());
                    }.bind(this));
                }.bind(this), {forced_root_block: this.forcedRootBlock});
            }
        },
        created: function () {
            console.log('created');
            this.$nextTick(function () {
                console.log('created nextTick');
                this.initEditor();
            }.bind(this));
        }
    }
</script>