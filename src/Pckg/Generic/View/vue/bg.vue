<template>
    <a v-if="url && display == 'popup'" :href="url" class="popup-iframe"></a>
    <div v-else-if="url" class="video-background">
        <div class="video-foreground">
            <iframe :src="url" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
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
                    if (true || this.display == 'background') {
                        return 'https://www.youtube-nocookie.com/embed/' + this.identifier
                            + '?controls=' + (this.action.settings.bgVideoControls == 'yes' ? 1 : 0)
                            + '&autoplay=' + (this.action.settings.bgVideoAutoplay == 'yes' ? 1 : 0)
                            + '&loop=' + (this.action.settings.bgVideoLoop == 'yes' ? 1 : 0)
                            + '&modestbranding=' + (this.action.settings.bgVideoBranding == 'yes' ? 1 : 0)
                            + (this.action.settings.bgVideoMute ? '&mute=1' : '')
                            + '&playsinline=1&rel=0&showinfo=0&playlist=' + this.identifier;
                    } else {
                        return 'https://www.youtube-nocookie.com/watch?v=' + this.identifier;
                    }
                } else if (this.source == 'vimeo') {
                    if (this.display == 'background') {
                        return 'https://player.vimeo.com/video/' + this.identifier
                            + '?controls=' + (this.action.settings.bgVideoControls == 'yes' ? 'true' : 'false')
                            + '&autoplay=' + (this.action.settings.bgVideoAutoplay == 'yes' ? 'true' : 'false')
                            + '&loop=' + (this.action.settings.bgVideoLoop == 'yes' ? 'true' : 'false')
                            + '&byline=' + (this.action.settings.bgVideoBranding == 'yes' ? 'true' : 'false')
                            + (this.action.settings.bgVideoMute ? '&muted=true' : '')
                            + '&dnt=true&playsinline=true&portrait=false&responsive=true';
                    } else {
                        return 'https://player.vimeo.com/video/' + this.identifier;
                    }
                }
            }
        }
    }
</script>