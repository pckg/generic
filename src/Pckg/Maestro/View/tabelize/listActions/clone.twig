{% macro relationTree(relations) %}
    {% if relations %}
        <ul>
            {% for relation in relations %}
                <li>
                    <input type="checkbox" value="{{ relation.id }}" v-model="relations"
                           @click="checkParent($event)"/> {{ relation.title }}
                    {% if relation.hasManyRelations.count() %}{{ _self.relationTree(relation.hasManyRelations) }}{% endif %}
                </li>
            {% endfor %}
        </ul>
    {% endif %}
{% endmacro %}

{% import _self as macros %}

<script type="text/x-template" id="pckg-dynamic-clone">
    <div>
        {% embed 'Pckg/Generic/View/modal.twig' with {'close': true, 'id': 'cloneRecordModal', 'class': 'danger'} %}
            {% block header %}
                clone record
            {% endblock %}
            {% block body %}
                <p>Do you really want to clone #${ record.id }?</p>

                {% if relations.count() %}
                    <p>Do you also want to clone related:</p>
                    {{ macros.relationTree(relations) }}
                {% endif %}

                <p><b>Number of clones: <input type="number" min="1" max="99" step="1" v-model="clones" class="form-control narrow"/></b></p>

                <p><a @click.prevent="cloneRecord" href="#" class="btn btn-danger">Yes, clone record</a></p>
            {% endblock %}
        {% endembed %}

        {% embed 'Pckg/Generic/View/modal.twig' with {'close': true, 'id': 'recordClonedModal'} %}
            {% block header %}
                Record cloned
            {% endblock %}
            {% block body %}
                <p>#${ record.id } was cloned.</p>
                <p><a :href="clonedUrl" class="btn btn-success">Open it</a></p>
            {% endblock %}
        {% endembed %}
    </div>
</script>

<script>
    var pckgDynamicClone = Vue.component('pckg-dynamic-clone', {
        mixins: [pckgDelimiters],
        name: 'pckg-dynamic-clone',
        template: '#pckg-dynamic-clone',
        data: function () {
            return {
                record: {},
                clonedUrl: null,
                relations: [],
                clones: 1
            };
        },
        methods: {
            checkCloneRecord: function (record) {
                this.record = record;

                $('#cloneRecordModal').modal('show');
            },
            cloneRecord: function () {
                $('#cloneRecordModal').modal('hide');

                http.post(this.record.cloneUrl, {clones: this.clones}, function (data) {
                    this.clonedUrl = data.clonedUrl;
                    $('#recordClonedModal').modal('show');
                }.bind(this));
            },
            checkParent: function ($event) {
                Vue.nextTick(function () {

                    if ($($event.target).is(':checked')) {
                        /**
                         * Check all parents.
                         */
                        var $parent = $($event.target);

                        while ($parent.parent().closest('li').length > 0) {
                            $parent = $parent.parent().closest('li');
                            if (!$parent.find('> input').is(':checked')) {
                                $parent.find('> input').trigger('click');
                            }
                        }
                    } else {
                        /**
                         * Uncheck all children.
                         */
                        $($event.target).parent().find('input:checked').click();
                    }

                });

                return true;
            }
        },
        created: function () {
            $dispatcher.$on('record:checkCloneRecord', this.checkCloneRecord);
        },
        beforeDestroy: function () {
            $dispatcher.$off('record:checkCloneRecord', this.checkCloneRecord);
        }
    });
</script>

<pckg-dynamic-clone></pckg-dynamic-clone>