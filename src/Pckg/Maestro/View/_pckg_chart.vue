<template>
    <canvas class="pckg-chart" id="pckg-chart-1" width="3" height="1"></canvas>
</template>

<script>
    export default {
        template: '#pckg-chart',
        props: {
            data: null,
            type: null,
            scale: {
                type: String,
                default: 'linear'
            }
        },
        data: function () {
            return {
                _chart: {}
            };
        },
        methods: {
            initChart: function () {
                if (this._chart) {
                    this._chart.destroy();
                }
                return; // chart is not available?

                this._chart = new Chart($(this.$el).get()[0], {
                    type: this.type,
                    data: this.data,
                    options: {
                        hover: {
                            mode: 'x-axis'
                        },
                        tooltips: {
                            mode: 'x-axis',
                            position: 'nearest'
                        },
                        scales: {
                            yAxes: [{
                                type: this.scale
                            }]
                        }
                    }
                });
            }
        },
        watch: {
            data: function () {
                this.initChart();
            },
            scale: function () {
                this.initChart();
            }
        },
        mounted: function () {
            /*Chart.Tooltip.positioners.leftTop = function(a){
                var average = this.average(a);

                if (average) {
                    average.y = 10;
                }

                return average;
            };*/
            this.$nextTick(this.initChart);
        }
    }
</script>
