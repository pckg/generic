<template>
    <div class="pckg-datetime-picker pckg-calendar">

        <div class="picker" :class="'mode-' + myMode">

            <template v-if="true || myMode != 'day'">
                <div class="as-table table-fixed">
                    <div>
                        <div class="as-table">
                            <div class="prev">
                                <button type="button" class="btn btn-default btn-sm pull-left" @click.prevent="prev">
                                    <i class="fa fa-chevron-left"></i>
                                </button>
                            </div>
                            <div class="title">
                                <button type="button" class="btn btn-default btn-sm btn-block" @click.prevent="zoomOut">
                                    {{ viewTitle }}
                                </button>
                            </div>
                            <div class="next">
                                <button type="button" class="btn btn-default btn-sm pull-right" @click.prevent="next">
                                    <i class="fa fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div></div>
                </div>

                <hr/>
            </template>

            <div class="interval-list">

                <template v-if="['century', 'decade', 'year'].indexOf(myMode) >= 0">
                    <button type="button" class="btn btn-default" v-for="(item, d) in range"
                            @click.prevent="select(d)">{{ item }}
                    </button>
                </template>

                <template v-else-if="myMode == 'month'">
                    <table class="table table-condensed mode-month">
                        <thead>
                        <tr>
                            <th v-for="weekDay in longWeekDays">{{ weekDay }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="week in weeks">
                            <td v-for="day in week">
                                <div class="scroll-wrap">
                                    <button class="btn btn-sm btn-default" type="button"
                                            @click.prevent="select(day.date)"
                                            :class="[day.transparent ? 'trans-fade' : '', day.active ? 'active' : '']"
                                            :disabled="day.disabled">{{ day.day }}
                                    </button>
                                    <div v-for="event in getDayEvents(day.date)" class="padding-vertical-xxs">
                                        <i :style="{color: event.color}" class="fas fa-circle "></i>
                                        <a href="#" @click.prevent><b>{{ event.start.format('HH:mm') }}</b> {{
                                            event.title }}</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </template>

                <template v-else-if="myMode == 'week'">
                    <table class="table table-condensed mode-week">
                        <thead>
                        <tr>
                            <th v-for="weekDay in longWeekDays">{{ weekDay }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td v-for="day in week">
                                <div class="scroll-wrap">
                                    <button class="btn btn-sm btn-default" type="button"
                                            @click.prevent="select(day.date)"
                                            :class="[day.transparent ? 'trans-fade' : '', day.active ? 'active' : '']"
                                            :disabled="day.disabled">{{ day.day }}
                                    </button>
                                    <div v-for="event in getDayEvents(day.date)" class="padding-vertical-xxs">
                                        <i :style="{color: event.color}" class="fas fa-circle "></i>
                                        <a href="#" @click.prevent><b>{{ event.start.format('HH:mm') }}</b> {{
                                            event.title }}</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </template>

                <div v-else-if="myMode == 'day'" style="position: relative;">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th v-for="group in groupedDayEvents(value)">
                                <i :style="{color: group[0].color}" class="fas fa-circle "></i>
                                {{ group[0].color }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td v-for="group in groupedDayEvents(value)">
                                <div class="scroll-wrap">
                                    <div v-for="event in group" class="padding-vertical-xxs" style="position: absolute;"
                                         :style="getEventStyle(event)">
                                        <i :style="{color: event.color}" class="fas fa-circle "></i>
                                        <a href="#" @click.prevent><b>{{ event.start.format('HH:mm') }}</b> {{
                                            event.title }}</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <template v-else-if="myMode == 'day'">
                    <button v-if="false" type="button" class="btn btn-default btn-sm btn-block"
                            @click.prevent="prevHour">
                        <i class="fa fa-chevron-up"></i>
                    </button>

                    <hr v-if="false"/>

                    <table class="table table-condensed mode-day table-fixed">
                        <tbody>
                        <tr v-for="hour in hours">
                            <td v-for="minute in hour">
                                <button class="btn btn-sm btn-default" type="button"
                                        @click.prevent="select(minute.time)"
                                        :class="[minute.transparent ? 'trans-fade' : '', minute.active ? 'active' : '']"
                                        :disabled="minute.disabled">
                                    {{ minute.time }}
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <hr v-if="false"/>

                    <button v-if="false" type="button" class="btn btn-default btn-sm btn-block"
                            @click.prevent="nextHour">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                </template>

            </div>

        </div>

    </div>
</template>

<script>
    export default {
        name: 'pckg-calendar',
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
                        type: 'week'
                    };
                }
            },
            value: {
                required: true
            }
        },
        data: function () {
            return {
                myMode: this.options.type == 'time' ? 'day' : 'day',
                views: {
                    century: 'Century',
                    decade: 'Decade',
                    year: 'Year',
                    month: 'Month',
                    week: 'Week',
                    day: 'Day'
                },
                _momentModel: this.moment(this.value || null),
                myValue: this.value,
            };
        },
        watch: {
            value: {
                immediate: true,
                handler: function (value) {
                    if (value == this.myValue) {
                        return;
                    }

                    console.log('changed from', this.myValue, 'to', value);
                    this._momentModel = this.moment(value);
                    this.myValue = value;
                }
            }
        },
        methods: {
            getEventStyle: function (event) {
                let hour = event.start.format('HH');
                let minute = event.start.format('mm');

                return {
                    top: (hour * (100 / 24)) + '%', // 0-23 = 0 - 100
                    left: (minute * (100 / 60) * 0.3) + '%', // 0 - 60
                    left: '0'
                };
            },
            moment: function (date) {
                console.log("parsing", date);
                let m = date ? moment(date) : moment();
                m.locale(this.options.locale || 'en');

                return m;
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

                this._momentModel.subtract(multiplier, mode);
                this.$emit('input', this._momentModel.format(this.options.format));
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

                this._momentModel.add(multiplier, mode);
                this.$emit('input', this._momentModel.format(this.options.format));
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
                    console.log('merged with current value', value);
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
            },
            groupedDayEvents: function (date) {
                let events = this.getDayEvents(date);
                let grouped = {};
                $.each(events, function (i, event) {
                    if (!grouped[event.color]) {
                        grouped[event.color] = [];
                    }

                    grouped[event.color].push(event);
                });

                return grouped;
            },
            getDayEvents: function (date) {
                let dateMoment = moment(date);
                return this.events.filter(function (event) {
                    if (event.start.isAfter(dateMoment, 'day')) {
                        return false;
                    }
                    if (event.end.isBefore(dateMoment, 'day')) {
                        return false;
                    }
                    if (event.start.isSameOrBefore(dateMoment, 'day') && event.end.isSameOrAfter(dateMoment, 'day')) {
                        return true;
                    }
                    if (event.start.isSame(dateMoment, 'day') || event.end.isSame(dateMoment, 'day')) {
                        return true;
                    }
                    return false;
                });
            }
        },
        created: function () {
            if (this.options.onCreated) {
                this.options.onCreated(this);
            }
        },
        computed: {
            events: function () {
                let events = [];
                let date = moment().add(-1, 'days');
                let i;
                let colors = ['red', 'green', 'blue', 'orange', 'yellow', 'purple', 'black', 'white'];
                for (i = 0; i < 1000; i++) {
                    date = moment(date.add(45, 'minutes').format());
                    let event = {
                        start: date,
                        end: date,
                        title: 'Some title #' + Math.floor(Math.random() * 1000),
                        color: colors[Math.floor(Math.random() * colors.length)],
                        link: '/maestro',
                    };
                    events.push(event);
                }

                return events;
            },
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
                } else if (this.myMode == 'week') {
                    return m.startOf('isoWeek').format('DD MMMM YYYY') + ' - ' + m.endOf('isoWeek').format('DD MMMM YYYY');
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
            longWeekDays: function () {
                let m = this.moment().startOf('isoWeek');
                let days = [];
                do {
                    days.push(m.format('dddd'));
                    m.add(1, 'day');
                } while (days.length < 7);

                return days;
            },
            weeks: function () {
                let m = this.moment(this.myValue);
                if (!m.isValid()) {
                    console.log('invalid value to calc weeks');
                    return [];
                }

                let startOfMonth = m.clone().startOf('month');
                let startOfWeek = startOfMonth.clone().startOf('isoWeek');
                let endOfMonth = m.clone().endOf('month');
                let endOfWeek = endOfMonth.clone().endOf('isoWeek');

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
            week: function () {
                let m = this.moment(this.myValue);
                if (!m.isValid()) {
                    return [];
                }

                let startOfWeek = m.clone().startOf('isoWeek');
                let endOfWeek = startOfWeek.clone().endOf('isoWeek');

                let week = [];
                let day = this.moment(startOfWeek);
                while (day.isBefore(endOfWeek)) {
                    week.push({
                        day: day.format('D'),
                        date: day.format('YYYY-MM-DD'),
                        disabled: this.options.checkEnabled && !this.options.checkEnabled(day, 'date'),
                        transparent: false,
                        active: day.format('YYYY-MM-DD') == m.format('YYYY-MM-DD'),
                    });

                    day.add(1, 'day');
                }

                return week;
            },
            hours: function () {
                let m = this.moment(this.myValue);
                if (!m.isValid()) {
                    console.log('invalid value to calc hours', this.myValue);
                    m = this.moment(this.moment().format('YYYY-MM-DD') + ' ' + this.myValue);
                }

                if (!m.isValid()) {
                    console.log('invalid computed value to calc hours', this.moment().format('YYYY-MM-DD') + ' ' + this.myValue);
                    return [];
                }

                console.log('building hour tree');
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

                console.log('returning', hours);

                return hours;
            }
        }
    }
</script>