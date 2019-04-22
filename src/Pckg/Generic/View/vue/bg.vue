<template>
    <div v-if="display == 'background' && identifier && source == 'youtube'" class="video-background">
        <div class="video-foreground">
            <iframe :src="url" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
    <a v-else-if="display == 'popup' && identifier && source == 'youtube'" :href="url" class="popup-iframe"></a>
</template>

<script>
    export default {
        name: 'pckg-action-bg',
        props: {
            action: {}
        },
        computed: {
            source: function () {
                if (!this.action) {
                    return false;
                }

                return this.action.settings.bgVideoSource || null;
            },
            display: function () {
                if (!this.action) {
                    return false;
                }

                return this.action.settings.bgVideoDisplay || 'background';
            },
            identifier: function () {
                if (!this.action) {
                    return false;
                }

                return this.action.settings.bgVideo || null;
            },
            url: function () {
                if (!this.identifier) {
                    return null;
                }

                if (this.source == 'youtube') {
                    if (this.display == 'background') {
                        return 'https://www.youtube.com/embed/' + this.identifier
                            + '?controls=' + (this.action.settings.bgVideoControls == 'yes' ? 1 : 0)
                            + '&autoplay=' + (this.action.settings.bgVideoAutoplay == 'yes' ? 1 : 0)
                            + '&loop=' + (this.action.settings.bgVideoLoop == 'yes' ? 1 : 0)
                            + (this.action.settings.bgVideoMute ? '&mute=1' : '')
                            + '&modestbranding=1&playsinline=1&rel=0&showinfo=0&playlist=' + this.identifier;
                    } else {
                        return 'https://www.youtube.com/watch?v=' + this.identifier;
                    }
                }
            }
        }
    }
</script>