const PckgDispatcherNotificationsComponent = () => import("../../View/dispatcherNotifications.vue");
const HtmlbuilderValidatorError = () => import ("../../View/htmlbuilderValidatorError.vue");
const PckgLoaderComponent = () => import ("../../View/pckgLoader.vue");
const PckgErroredComponent = () => import ("../../View/pckgErrored.vue");
const PckgDatetimeComponent = () => import ("../../View/pckgDatetime.vue");
const PckgBootstrapAlertComponent = () => import ("../../../Maestro/public/vue/pckg-bootstrap-alert.vue");
const PckgBootstrapModalComponent = () => import ("../../../Maestro/public/vue/pckg-bootstrap-modal.vue");
const PckgBootstrapModalBackdropComponent = () => import ("../../../Maestro/public/vue/pckg-bootstrap-modal-backdrop.vue");
const PckgBootstrapBlockComponent = () => import ("../../../Maestro/public/vue/pckg-bootstrap-block.vue");
const PckgBootstrapSidebarComponent = () => import ("../../../Maestro/public/vue/pckg-bootstrap-sidebar.vue");
const PckgHtmlbuilderGeoComponent = () => import ("../../../Maestro/public/vue/pckg-htmlbuilder-geo.vue");
const PckgDatetimePicker = () => import ("../../View/pckg-datetime-picker.vue");
const PckgCalendar = () => import ("../../View/pckg-calendar.vue");

export default {
    install(Vue) {
        Vue.component('htmlbuilder-validator-error', HtmlbuilderValidatorError);
        Vue.component('pckg-loader', PckgLoaderComponent);
        Vue.component('pckg-errored', PckgErroredComponent);
        Vue.component('pckg-datetime', PckgDatetimeComponent);
        Vue.component('pckg-datetime-picker', PckgDatetimePicker);
        Vue.component('pckg-dispatcher-notifications', PckgDispatcherNotificationsComponent);
        Vue.component('pckg-bootstrap-alert', PckgBootstrapAlertComponent);
        Vue.component('pckg-bootstrap-modal', PckgBootstrapModalComponent);
        Vue.component('pckg-bootstrap-modal-backdrop', PckgBootstrapModalBackdropComponent);
        Vue.component('pckg-bootstrap-block', PckgBootstrapBlockComponent);
        Vue.component('pckg-bootstrap-sidebar', PckgBootstrapSidebarComponent);
        Vue.component('pckg-htmlbuilder-geo', PckgHtmlbuilderGeoComponent);
        Vue.component('pckg-calendar', PckgCalendar);
    }
}
