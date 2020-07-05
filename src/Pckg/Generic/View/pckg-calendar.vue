<template>
    <div class="pckg-calendar">

        <div class="picker" :class="'mode-' + myMode">

            <template v-if="true || myMode != 'day'">
                <div class="as-table table-fixed">
                    <div>
                        <div class="as-table">
                            <div class="prev">
                                <button type="button" class="btn btn-default btn-sm pull-left" @click.prevent="prev">
                                    <i class="fal fa-chevron-left"></i>
                                </button>
                            </div>
                            <div class="title">
                                <button type="button" class="btn btn-default btn-sm btn-block" @click.prevent="zoomOut">
                                    {{ viewTitle }}
                                </button>
                            </div>
                            <div class="next">
                                <button type="button" class="btn btn-default btn-sm pull-right" @click.prevent="next">
                                    <i class="fal fa-chevron-right"></i>
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

                <div v-else-if="myMode == 'month'" style="position: relative; overflow: auto;">
                    <!-- calendar view -->
                    <table class="table mode-month table-fixed">
                        <thead>
                        <tr>
                            <th v-for="weekDay in longWeekDays">{{ weekDay.name }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="week in weeks">
                            <td v-for="day in week" class="__day_width">
                                <div class="height-wrapper" @click.self="select(day.date)">
                                    <span class="__day"
                                          @click.prevent="select(day.date)"
                                          :class="[day.transparent ? 'trans-fade' : '', day.active ? 'active' : '']"
                                          :disabled="day.disabled">{{ day.day }}
                                    </span>
                                    <div v-for="event in getDayEvents(day.date).slice(0, 3)" class="__event">
                                        <i :style="{color: getEventColor(event)}" class="fal fa-circle"></i>
                                        <a href="#" @click.prevent>{{ event.start.format('HH:mm') }} {{
                                            event.title }}</a>
                                    </div>
                                    <span class="__more" v-if="getDayEvents(day.date).length > 3">{{ getDayEvents(day.date).length - 3 }} more</span>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else-if="myMode == 'week'" style="position: relative; overflow: auto;" class="height-wrapper">
                    <!-- weekly view -->
                    <table class="table mode-week table-fixed">
                        <thead>
                        <tr>
                            <th v-for="weekDay in longWeekDays">{{ weekDay.name }}<br/>{{ weekDay.m | date }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td v-for="day in week" class="__day_width">
                                <div @click.self="select(day.date)">
                                    <span class="__day"
                                          @click.prevent="select(day.date)"
                                          :class="[day.transparent ? 'trans-fade' : '', day.active ? 'active' : '']"
                                          :disabled="day.disabled">{{ day.day }}
                                    </span>
                                    <div v-for="event in getDayEvents(day.date)" class="__event">
                                        <i :style="{color: getEventColor(event)}" class="fal fa-circle"></i>
                                        <a href="#" @click.prevent>{{ event.start.format('HH:mm') }} {{
                                            event.title }}</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else-if="myMode == 'day'" style="position: relative; overflow: auto;">
                    <!-- daily view -->
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="no-border"></th>
                            <th v-for="events in groupedDayEvents()">
                                <i :style="{color: keyedGroups[events[0].group].color}" class="fal fa-circle"></i>
                                {{ keyedGroups[events[0].group].title }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(minutes, hour) in hours">
                            <td class="no-border __hour_cell"><span class="__hour">{{ hour }}:00</span></td>
                            <td v-for="group in groupedDayEvents(hour)" class="__day_width" :class="getCellClass(hour)">
                                <div class="height-wrapper" @dblclick.prevent="emitFinalClick($event, hour)">
                                    <div v-for="event in group" class="__event">
                                        <i :style="{color: getEventColor(event)}" class="fal fa-circle"></i>
                                        <a href="#" @click.prevent>{{ event.start.format('HH:mm') }} {{
                                            event.title }}</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

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
            },
            events: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            groups: {
                type: Array,
                default: function () {
                    return [];
                }
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

                    this.$data._momentModel = this.moment(value);
                    this.myValue = value;
                }
            }
        },
        methods: {
            getEventColor: function (event) {
                let group = this.keyedGroups[event.group] || null;
                if (!group) {
                    return 'transparent';
                }

                return group.color;
            },
            getCellClass: function (hour) {
                if (parseInt(hour) > 7) {
                    return 'bg-success';
                }

                return null;
            },
            emitFinalClick: function ($event, hour) {
                this.$emit('final-click', this.$data._momentModel.format('YYYY-MM-DD') + ' ' + hour + ':00');
            },
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
                let m = date ? moment(date) : moment();
                m.locale(this.options.locale || 'en');

                return m;
            },
            setAndEmitValue(value) {
                this.$data._momentModel = moment(value);
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
                this.setAndEmitValue(value);

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
            groupedDayEvents: function (hour) {
                let events = this.getDayEvents(this.$data._momentModel);
                let grouped = {};
                $.each(events, function (i, event) {
                    if (!grouped[event.group]) {
                        grouped[event.group] = [];
                    }

                    grouped[event.group].push(event);
                });

                if (typeof hour != 'undefined') {
                    $.each(grouped, function (group, events) {
                        grouped[group] = events.filter(function (event) {
                            if (event.start.format('H') != hour) {
                                return false;
                            }
                            if (event.start.format('H') != hour) {
                                return false;
                            }
                            return true;
                        });
                    });
                }

                return grouped;
            },
            getDayEvents: function (date, hour) {
                return this.events.filter(function (event) {
                    if (event.start.isAfter(date, 'day')) {
                        return false;
                    }
                    if (event.end && event.end.isBefore(date, 'day')) {
                        return false;
                    }
                    if (false && event.start.isSameOrBefore(date, 'day') && event.end && event.end.isSameOrAfter(date, 'day')) {
                        return true;
                    }
                    if (event.start.isSame(date, 'day') || (false && event.end && event.end.isSame(date, 'day'))) {
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
            keyedGroups: function () {
                let groups = {};
                $.each(this.groups, function (i, group) {
                    groups[group.id] = group;
                });
                return groups;
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
                    days.push({name: m.format('dddd'), m: m});
                    m = m.clone().add(1, 'day');
                } while (days.length < 7);

                return days;
            },
            weeks: function () {
                let m = this.moment(this.myValue);
                if (!m.isValid()) {
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
                        hour: minute.format('H'),
                        Hour: minute.format('HH'),
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