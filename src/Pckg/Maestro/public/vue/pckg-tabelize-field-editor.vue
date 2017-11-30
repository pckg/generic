<template>
    <div v-html="strippedContent"></div>
</template>

<script>
    export default {
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
    }
</script>