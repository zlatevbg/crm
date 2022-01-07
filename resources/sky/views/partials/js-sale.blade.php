$.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
$.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
$('#input-promissory_at').datepicker();

var project = document.querySelector('#input-project_id');
var selectedProject = '{{ $api->model->project_id ?: session('project') }}';
var apartment = document.querySelector('#input-apartment_id');
var selectedApartment = '{{ $api->model->apartment_id ?: '' }}';
var price = document.querySelector('#input-price');
var furniture = document.querySelector('#input-furniture');
var client = document.querySelector('#input-client_id');
var selectedClient = '{{ $api->model->client_id ?: '' }}';
var agent = document.querySelector('#input-agent_id');
var selectedAgent = '{{ $api->model->agent_id ?: '' }}';
var subagent = document.querySelector('#input-subagent_id');
var selectedSubAgent = '{{ $api->model->subagent_id ?: '' }}';
var commission = document.querySelector('#input-commission');
var subCommission = document.querySelector('#input-sub_commission');
var percent = {{ $api->model->project_id ? $api->model->getCommission() : 0 }};
var subPercent = {{ $api->model->project_id ? $api->model->getSubCommission() : 0 }};
var amount = {{ $api->model->price ? ($api->model->price + $api->model->furniture) : 0 }};
var event = new Event('input', {
    bubbles: true,
    cancelable: true,
});

@if (!session('project'))
    project.addEventListener('change', function() {
        price.disabled = true;
        price.value = '';
        furniture.disabled = true;
        furniture.value = '';
        client.disabled = true;
        client.selectedIndex = 0;
        $(client).multiselect('refresh');
        $(client).multiselect('disable');
        agent.disabled = true;
        agent.selectedIndex = 0;
        $(agent).multiselect('refresh');
        $(agent).multiselect('disable');
        percent = 0;
        subPercent = 0;
        commission.disabled = true;
        commission.value = '';
        subCommission.disabled = true;
        subCommission.value = '';

        if (parseInt(this.value) != selectedProject) {
            if (parseInt(this.value) > 0) {
                selectedProject = this.value;
                ajax.ajaxify({
                    obj: this,
                    method: 'get',
                    queue: 'sync',
                    action: '{{ Helper::route('api.load-data') }}',
                    data: 'method=ApartmentsAgentsClients&exclude=sales&project=' + this.value + '&subagents=true',
                    skipErrors: true,
                }).then(function (data) {
                    while (apartment.firstChild) {
                        apartment.removeChild(apartment.firstChild);
                    }

                    $(apartment).append($('<option selected="selected"></option>').attr('value', 0).text('@lang('multiselect.noneSelectedSingle')'));
                    $.each(data.data.apartments, function(key, value) {
                        $(apartment).append($('<option data-price="' + value.price  + '" data-furniture="' + value.furniture  + '"></option>').attr('value', value.id).text(value.apartment));
                    });

                    apartment.disabled = false;
                    $(apartment).multiselect('enable');
                    $(apartment).multiselect('refresh');

                    while (client.firstChild) {
                        client.removeChild(client.firstChild);
                    }

                    $.each(data.data.clients, function(key, value) {
                        $(client).append($('<option data-agent="' + value.agent_id  + '"></option>').attr('value', value.id).text(value.client));
                    });

                    client.disabled = false;
                    $(client).multiselect('enable');
                    $(client).multiselect('refresh');

                    while (agent.firstChild) {
                        agent.removeChild(agent.firstChild);
                    }

                    $.each(data.data.agents, function(key, value) {
                        $(agent).append($('<option data-agent="' + value.agent_id  + '"></option>').attr('value', value.id).text(value.agent));
                    });

                    agent.disabled = true;
                    $(agent).multiselect('disable');
                    $(agent).multiselect('refresh');

                    while (subagent.firstChild) {
                        subagent.removeChild(subagent.firstChild);
                    }

                    $.each(data.data.subagents, function(key, value) {
                        $(subagent).append($('<option></option>').attr('value', value.id).text(value.agent));
                    });

                    subagent.disabled = true;
                    $(subagent).multiselect('disable');
                    $(subagent).multiselect('refresh');
                }).catch(function (error) {
                });
            } else {
                while (apartment.firstChild) {
                    apartment.removeChild(apartment.firstChild);
                }

                apartment.disabled = true;
                $(apartment).multiselect('disable');
                $(apartment).multiselect('refresh');

                while (client.firstChild) {
                    client.removeChild(client.firstChild);
                }

                client.disabled = true;
                $(client).multiselect('disable');
                $(client).multiselect('refresh');

                while (agent.firstChild) {
                    agent.removeChild(agent.firstChild);
                }

                agent.disabled = true;
                $(agent).multiselect('disable');
                $(agent).multiselect('refresh');

                while (subagent.firstChild) {
                    subagent.removeChild(subagent.firstChild);
                }

                subagent.disabled = true;
                $(subagent).multiselect('disable');
                $(subagent).multiselect('refresh');
            }
        }
    });
@endif

$(apartment).multiselect({
    multiple: false,
    close: function() {
        if (parseInt(this.value) != selectedApartment) {
            if (parseInt(this.value) > 0) {
                selectedApartment = this.value;
                furniture.disabled = false;
                furniture.value = this.options[this.selectedIndex].getAttribute('data-furniture');
                price.disabled = false;
                price.value = this.options[this.selectedIndex].getAttribute('data-price');
                amount = parseInt(price.value) + parseInt(furniture.value);
                price.dispatchEvent(event);
                client.disabled = false;
-               $(client).multiselect('enable');
            } else {
                price.value = '';
                price.disabled = true;
                furniture.value = '';
                furniture.disabled = true;
                client.disabled = true;
                client.selectedIndex = 0;
                $(client).multiselect('refresh');
                $(client).multiselect('disable');
                agent.disabled = true;
                agent.selectedIndex = 0;
                $(agent).multiselect('refresh');
                $(agent).multiselect('disable');
                subagent.disabled = true;
                subagent.selectedIndex = 0;
                $(subagent).multiselect('refresh');
                $(subagent).multiselect('disable');
                percent = 0;
                subPercent = 0;
                commission.disabled = true;
                commission.value = '';
                subCommission.disabled = true;
                subCommission.value = '';
            }
        }
    },
});

price.addEventListener('input', function(e) {
    amount = parseInt(this.value) + parseInt(furniture.value);
    if (percent && parseInt(this.value) > 0) {
        commission.value = ((amount / 100) * percent).toFixed(2);
    }

    if (subPercent && parseInt(this.value) > 0) {
        subCommission.value = ((amount / 100) * subPercent).toFixed(2);
    }
});

furniture.addEventListener('input', function(e) {
    amount = parseInt(this.value) + parseInt(price.value);
    if (percent && parseInt(this.value) > 0) {
        commission.value = ((amount / 100) * percent).toFixed(2);
    }

    if (subPercent && parseInt(this.value) > 0) {
        subCommission.value = ((amount / 100) * subPercent).toFixed(2);
    }
});

$(client).multiselect({
    multiple: false,
    close: function() {
        if (parseInt(this.value) != selectedClient) {
            if (parseInt(this.value) > 0) {
                selectedClient = this.value;
                var dataAgent = this.options[this.selectedIndex].getAttribute('data-agent');
                ajax.ajaxify({
                    obj: this,
                    method: 'get',
                    queue: 'sync',
                    action: '{{ Helper::route('api.load-data') }}',
                    data: 'method=Commission&agent=' + dataAgent + '&project=' + selectedProject,
                    skipErrors: true,
                }).then(function (data) {
                    percent = parseFloat(data.data);
                    if (amount && percent > 0) {
                        commission.value = ((amount / 100) * percent).toFixed(2);
                    }

                    if (commission.value) {
                        commission.disabled = false;
                    }
                }).catch(function (error) {
                });

                agent.value = dataAgent;
                agent.disabled = false;
                $(agent).multiselect('enable');
                $(agent).multiselect('refresh');
                subagent.disabled = false;
                $(subagent).multiselect('enable');
            } else {
                agent.value = 0;
                agent.disabled = true;
                agent.selectedIndex = 0;
                $(agent).multiselect('refresh');
                $(agent).multiselect('disable');
                commission.disabled = true;
                commission.value = '';
                percent = 0;
                subagent.disabled = true;
                subagent.selectedIndex = 0;
                $(subagent).multiselect('refresh');
                $(subagent).multiselect('disable');
                subCommission.disabled = true;
                subCommission.value = '';
            }
        }
    },
});

$(agent).multiselect({
    multiple: false,
    close: function() {
        if (parseInt(this.value) != selectedAgent) {
            if (parseInt(this.value) > 0) {
                selectedAgent = this.value;
                ajax.ajaxify({
                    obj: this,
                    method: 'get',
                    queue: 'sync',
                    action: '{{ Helper::route('api.load-data') }}',
                    data: 'method=Commission&agent=' + this.value + '&project=' + selectedProject,
                    skipErrors: true,
                }).then(function (data) {
                    percent = data.data;
                    if (amount) {
                        commission.value = ((amount / 100) * percent).toFixed(2);
                    }
                }).catch(function (error) {
                });

                commission.disabled = false;
            } else {
                commission.disabled = true;
                commission.value = '';
                percent = 0;
            }
        }
    },
});

$(subagent).multiselect({
    multiple: false,
    close: function() {
        if (parseInt(this.value) != selectedSubAgent) {
            if (parseInt(this.value) > 0) {
                selectedSubAgent = this.value;
                ajax.ajaxify({
                    obj: this,
                    method: 'get',
                    queue: 'sync',
                    action: '{{ Helper::route('api.load-data') }}',
                    data: 'method=SubCommission&subagent=' + this.value + '&project=' + selectedProject,
                    skipErrors: true,
                }).then(function (data) {
                    subPercent = data.data;
                    if (amount) {
                        subCommission.value = ((amount / 100) * subPercent).toFixed(2);
                    }
                }).catch(function (error) {
                });

                subCommission.disabled = false;
            } else {
                subCommission.disabled = true;
                subCommission.value = '';
                subPercent = 0;
            }
        }
    },
});
