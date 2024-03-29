<template>
    <div class="pckg-datetime-picker clearfix">

        <input type="text" class="form-control" v-model="myValue" @focus="focused" :placeholder="placeholder"
               @change="$emit('input', myValue)"/>

        <div class="picker" v-if="visible" :class="'mode-' + myMode">

            <template v-if="true || myMode != 'day'">
                <div class="as-table">
                    <div class="prev">
                        <button type="button" class="button size-xs pull-left btn-bordered" @click.prevent="prev">
                            <i class="fal fa-chevron-left"></i>
                        </button>
                    </div>
                    <div class="title">
                        <button type="button" class="button size-xs btn-block btn-bordered" @click.prevent="zoomOut">
                            {{ viewTitle }}
                        </button>
                    </div>
                    <div class="next">
                        <button type="button" class="button size-xs pull-right btn-bordered" @click.prevent="next">
                            <i class="fal fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <hr/>
            </template>

            <div class="interval-list">

                <template v-if="['century', 'decade', 'year'].indexOf(myMode) >= 0">
                    <div v-for="(item, d) in range" class="__interval-item">
                        <button type="button" class="button size-xs btn-block btn-bordered" @click.prevent="select(d)">{{ item }}</button>
                    </div>
                </template>

                <template v-else-if="myMode == 'month'">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th v-for="weekDay in weekDays">{{ weekDay }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="week in weeks">
                            <td v-for="day in week">
                                <button class="button size-xs btn-block" type="button" @click.prevent="select(day.date)"
                                        :class="[day.transparent ? 'trans-fade' : '', day.active ? 'active' : 'btn-bordered']"
                                        :disabled="day.disabled">{{ day.day }}
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </template>

                <template v-else-if="myMode == 'day'">
                    <button v-if="false" type="button" class="button size-xs btn-block btn-bordered"
                            @click.prevent="prevHour">
                        <i class="fal fa-chevron-up"></i>
                    </button>

                    <hr v-if="false"/>

                    <table class="table table-condensed">
                        <tbody>
                        <tr v-for="hour in hours">
                            <td v-for="minute in hour">
                                <button class="button size-xs btn-block" type="button"
                                        @click.prevent="select(minute.time)"
                                        :class="[minute.transparent ? 'trans-fade' : '', minute.active ? 'active' : 'btn-bordered']"
                                        :disabled="minute.disabled">
                                    {{ minute.time }}
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <hr v-if="false"/>

                    <button v-if="false" type="button" class="button size-xs btn-block btn-bordered"
                            @click.prevent="nextHour">
                        <i class="fal fa-chevron-down"></i>
                    </button>
                </template>

            </div>

        </div>

    </div>
</template>

<script>
import Moment from "moment-timezone/builds/moment-timezone.min";
    export default {
        name: 'pckg-datetime-picker',
        props: {
            localDispatcher: {
                default: null
            },
            placeholder: {
                type: String
            },
            options: {
                type: Object,
                default: function () {
                    return {
                        format: 'YYYY-MM-DD',
                        type: 'date',
                        checkEnabled: null
                    };
                }
            },
            value: {
                default: ''
            }
        },
        model: {
            prop: 'value',
        },
        data: function () {
            return {
                myMode: this.options.type == 'time' ? 'day' : 'month',
                views: {
                    century: 'Century',
                    decade: 'Decade',
                    year: 'Year',
                    month: 'Month',
                    day: 'Day'
                },
                _momentModel: this.moment(this.value || null),
                myValue: this.value,
                visible: false
            };
        },
        watch: {
            value: {
                immediate: true,
                handler: function (value) {
                    if (value == this.myValue) {
                        return;
                    }

                    this.$data._momentModel = this.moment(value);
                    this.myValue = value;
                }
            }
        },
        methods: {
            moment: function (date) {
                let m = date ? Moment(date) : Moment();
                m.locale(this.options.locale || 'en');

                return m;
            },
            close: function () {
                this.visible = false;
                this.$emit('closed');
            },
            show: function () {
                this.visible = true;
                this.$emit('opened');
            },
            focused: function () {
                this.visible = true;
                if (!this.myValue || this.myValue.length == 0) {
                    if (this.options.getDefault) {
                        this.setAndEmitValue(this.options.getDefault());
                    } else {
                        this.setAndEmitValue(this.moment().format(this.options.format));
                    }
                }
            },
            setAndEmitValue(value) {
                this.myValue = value;
                this.$emit('input', value);
            },
            prev: function () {
                let mode = this.myMode;
                let multiplier = 1;

                if (mode == 'decade') {
                    multiplier = 10;
                    mode = 'years';
                } else if (mode == 'century') {
                    multiplier = 100;
                    mode = 'years';
                }

                this.$data._momentModel.subtract(multiplier, mode);
                this.setAndEmitValue(this.$data._momentModel.format(this.options.format));
            },
            next: function () {
                let mode = this.myMode;
                let multiplier = 1;

                if (mode == 'decade') {
                    multiplier = 10;
                    mode = 'years';
                } else if (mode == 'century') {
                    multiplier = 100;
                    mode = 'years';
                }

                this.$data._momentModel.add(multiplier, mode);
                this.setAndEmitValue(this.$data._momentModel.format(this.options.format));
            },
            zoomOut: function () {
                if (this.options.type == 'time' && this.myMode == 'day') {
                    return;
                }

                let prev = null;
                $.each(this.views, function (v, t) {
                    if (v == this.myMode) {
                        if (prev) {
                            this.myMode = prev;
                        }
                        return false;
                    }
                    prev = v;
                }.bind(this));
            },
            format: function (date) {
                return date.format(this.options.format);
            },
            select: function (value) {
                let mode = this.myMode;
                let format = this.options.format;
                let currentValue = this.myValue;

                if (mode == 'day' && currentValue && value && value.length < format.length) {
                    value = currentValue.split(' ')[0] + ' ' + value;
                } else if (currentValue && currentValue.length >= format.length && value && value.length < format.length) {
                    value = value + '' + currentValue.substring(value.length);
                }
                this.$emit('input', value);

                /**
                 * Y-m-d H:i:s     null
                 * Y-m-d           null
                 * H:i             null
                 *
                 */

                if (this.options.type == 'date' && this.myMode == 'month') {
                    this.close();
                    if (this.options.onSelected) {
                        this.options.onSelected(this);
                    }
                    return;
                } else if (['time', 'datetime'].indexOf(this.options.type) >= 0 && this.myMode == 'day') {
                    this.close();
                    if (this.options.onSelected) {
                        this.options.onSelected(this);
                    }
                    return;
                }

                let prev = false;
                $.each(this.views, function (v, t) {
                    if (prev) {
                        this.myMode = v;
                        return false;
                    } else if (v == this.myMode) {
                        prev = v;
                    }
                }.bind(this));
            }
        },
        created: function () {
            if (this.options.onCreated) {
                this.options.onCreated(this);
            }
            let $t = this;

            $('body').on('click', function (e) {
                let closest = $(e.target).closest('.pckg-datetime-picker');

                if (!closest || closest.length == 0 || !closest.is($($t.$el))) {
                    $t.visible = false;
                }
            });
        },
        computed: {
            viewTitle: function () {
                let m = this.moment(this.myValue);
                if (!m.isValid()) {
                    return '';
                }

                let year = m.format('YYYY');
                if (this.myMode == 'century') {
                    let start = year - (year % 100);
                    let end = start + 99;

                    return start + ' - ' + end;
                } else if (this.myMode == 'decade') {
                    let start = year - (year % 10);
                    let end = start + 9;

                    return start + ' - ' + end;
                } else if (this.myMode == 'year') {
                    return year;
                } else if (this.myMode == 'month') {
                    return m.format('MMMM YYYY');
                } else if (this.myMode == 'day') {
                    return m.format('DD MMMM YYYY');
                }
            },
            range: function () {
                let m = this.moment(this.myValue);
                if (!m.isValid()) {
                    return {};
                }

                let range = {};
                let year = m.format('YYYY');
                let month = m.format('MM');
                let day = m.format('DD');
                let hour = m.format('HH');
                let minute = m.format('mm');

                if (this.myMode == 'century') {
                    let start = year - (year % 100);
                    let end = start + 99;
                    for (; start <= end; start += 10) {
                        range[this.format(this.moment(start + '-' + month + '-' + day))] = start + ' - ' + (start + 9);
                    }
                } else if (this.myMode == 'decade') {
                    let start = year - (year % 10);
                    let end = start + 9;

                    for (; start <= end; start++) {
                        range[this.format(this.moment(start + '-' + month + '-' + day))] = start;
                    }
                } else if (this.myMode == 'year') {
                    let date = year + '-' + month + '-' + day;
                    let start = this.moment(date).startOf('year');
                    let end = start.clone().endOf('year');

                    while (start.isBefore(end)) {
                        range[this.format(start)] = start.format('MMMM');
                        start.add(1, 'month');
                    }
                } else if (this.myMode == 'day') {
                    let date = year + '-' + month + '-' + day + ' ' + hour + ':' + minute;
                    let start = this.moment(date).startOf('day');
                    let end = start.clone().endOf('day');

                    while (start.isBefore(end)) {
                        range[start.format('HH:mm')] = start.format('HH');
                        start.add(1, 'hour');
                    }
                } else if (this.myMode == 'hour') {
                    let date = year + '-' + month + '-' + day + ' ' + hour + ':' + minute;
                    let start = this.moment(date).startOf('day');
                    let end = start.clone().endOf('day');

                    while (start.isBefore(end)) {
                        range[start.format('HH:mm')] = start.format('mm');
                        start.add(5, 'minutes');
                    }
                }

                return range;
            },
            weekDays: function () {
                let m = this.moment().startOf('week');
                let days = [];
                do {
                    days.push(m.format('dd'));
                    m.add(1, 'day');
                } while (days.length < 7);

                return days;
            },
            weeks: function () {
                let m = this.moment(this.myValue);
                if (!m.isValid()) {
                    return [];
                }

                let startOfMonth = m.clone().startOf('month');
                let startOfWeek = startOfMonth.clone().startOf('week');
                let endOfMonth = m.clone().endOf('month');
                let endOfWeek = endOfMonth.clone().endOf('week');

                let weeks = [];
                let week = [];
                let day = this.moment(startOfWeek);
                while (day.isBefore(endOfWeek)) {
                    if (week.length == 7) {
                        weeks.push(week);
                        week = [];
                    }

                    week.push({
                        day: day.format('D'),
                        date: day.format('YYYY-MM-DD'),
                        disabled: this.options.checkEnabled && !this.options.checkEnabled(day, 'date'),
                        transparent: day.format('MM') != startOfMonth.format('MM'),
                        active: day.format('YYYY-MM-DD') == m.format('YYYY-MM-DD'),
                    });

                    day.add(1, 'day');
                }

                if (week.length > 0) {
                    weeks.push(week);
                    week = [];
                }

                return weeks;
            },
            hours: function () {
                let m = this.moment(this.myValue);
                if (!m.isValid()) {
                    m = this.moment(this.moment().format('YYYY-MM-DD') + ' ' + this.myValue);
                }

                if (!m.isValid()) {
                    return [];
                }

                let c = {min: 0, max: 23};
                let startOfDay = m.clone().startOf('day').hour(c.min);
                let endOfDay = m.clone().endOf('day').hour(c.max);

                let hours = [];
                let hour = [];
                let minute = this.moment(startOfDay);
                let available = false;
                while (minute.isBefore(endOfDay)) {
                    let hourAvailable = !this.options.checkEnabled || this.options.checkEnabled(minute, 'time');
                    let item = {
                        minute: minute.format('mm'),
                        time: minute.format('HH:mm'),
                        disabled: !hourAvailable,
                        transparent: false,
                        active: minute.format('HH:mm') == m.format('HH:mm'),
                    };
                    hour.push(item);
                    if (hourAvailable) {
                        available = true;
                    }

                    if (hour.length == 12) {
                        if (available) {
                            hours.push(hour);
                            available = false;
                        }
                        hour = [];
                    }

                    minute.add(5, 'minutes');
                }

                if (available && hour.length > 0) {
                    hours.push(hour);
                    hour = [];
                }

                return hours;
            }
        }
    }
</script>