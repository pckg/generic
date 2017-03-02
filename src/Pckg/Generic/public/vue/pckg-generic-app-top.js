/**
 * Register main Vue event dispatcher
 * @type {Vue}
 */
$dispatcher = new Vue();

var data = data || {};

var props = props || {};

var pckgDelimiters = {
    delimiters: ['${', '}']
};

var pckgTimeout = {
    methods: {
        timeout: function (name, callback, timeout) {
            if (this['_pckgTimeout' + name]) {
                clearTimeout(this['_pckgTimeout' + name]);
            }

            this['_pckgTimeout' + name] = setTimeout(callback, timeout);
        }
    }
};