var group = document.querySelector('#input-group');
var goldenvisa = document.querySelector('#input-goldenvisa');
var projects = document.querySelector('#input-projects');
var status = document.querySelector('#input-status');
var source = document.querySelector('#input-source');
var recipients = document.querySelector('#input-recipients');
var selectedGroup = '{{ $api->model->group ?: '' }}';
var selectedGoldenvisa = '{{ ($api->model->goldenvisa === 0 || $api->model->goldenvisa === 1) ? $api->model->goldenvisa : '' }}';
var selectedSource = @json($api->model->source);
var selectedStatus = @json($api->model->status);
var selectedProjects = @json($api->model->projects);

group.addEventListener('change', function() {
    if (parseInt(this.value) != selectedGroup) {
        selectedGroup = this.value;

        if (this.value) {
            ajax.ajaxify({
                obj: this,
                method: 'get',
                queue: 'sync',
                action: '{{ Helper::route('api.load-data') }}',
                data: 'method=Recipients&group=' + this.value + (selectedGoldenvisa == '' ? '' : '&goldenvisa=' + selectedGoldenvisa) + '&include-source&include-status&include-projects',
                skipErrors: true,
            }).then(function (data) {
                if (selectedGroup == 'agent-contacts') {
                    goldenvisa.closest('#goldenvisa').classList.remove('table-hidden');
                } else {
                    goldenvisa.closest('#goldenvisa').classList.add('table-hidden');
                }

                while (recipients.firstChild) {
                    recipients.removeChild(recipients.firstChild);
                }

                $.each(data.data.recipients, function(key, value) {
                    $(recipients).append($('<option></option>').attr('value', value.id).text(value.recipient));
                });

                recipients.disabled = false;
                $(recipients).multiselect('enable');
                $(recipients).multiselect('refresh');

                if (data.data.source) {
                    source.closest('#source').classList.remove('table-hidden');

                    while (source.firstChild) {
                        source.removeChild(source.firstChild);
                    }

                    $.each(data.data.source, function(key, value) {
                        $(source).append($('<option></option>').attr('value', value.id).text(value.source));
                    });

                    source.disabled = false;
                    $(source).multiselect('enable');
                    $(source).multiselect('refresh');
                } else {
                    while (source.firstChild) {
                        source.removeChild(source.firstChild);
                    }

                    source.disabled = true;
                    $(source).multiselect('disable');
                    $(source).multiselect('refresh');
                    source.closest('#source').classList.add('table-hidden');
                }

                if (data.data.status) {
                    status.closest('#client-status').classList.remove('table-hidden');

                    while (status.firstChild) {
                        status.removeChild(status.firstChild);
                    }

                    $.each(data.data.status, function(key, value) {
                        $(status).append($('<option></option>').attr('value', value.id).text(value.status));
                    });

                    status.disabled = false;
                    $(status).multiselect('enable');
                    $(status).multiselect('refresh');
                } else {
                    while (status.firstChild) {
                        status.removeChild(status.firstChild);
                    }

                    status.disabled = true;
                    $(status).multiselect('disable');
                    $(status).multiselect('refresh');
                    status.closest('#client-status').classList.add('table-hidden');
                }

                if (data.data.projects) {
                    projects.closest('#client-projects').classList.remove('table-hidden');

                    while (projects.firstChild) {
                        projects.removeChild(projects.firstChild);
                    }

                    $.each(data.data.projects, function(key, value) {
                        $(projects).append($('<option></option>').attr('value', value.id).text(value.projects));
                    });

                    projects.disabled = false;
                    $(projects).multiselect('enable');
                    $(projects).multiselect('refresh');
                } else {
                    while (projects.firstChild) {
                        projects.removeChild(projects.firstChild);
                    }

                    projects.disabled = true;
                    $(projects).multiselect('disable');
                    $(projects).multiselect('refresh');
                    projects.closest('#client-projects').classList.add('table-hidden');
                }
            }).catch(function (error) {
            });
        } else {
            goldenvisa.closest('#goldenvisa').classList.add('table-hidden');

            while (recipients.firstChild) {
                recipients.removeChild(recipients.firstChild);
            }

            recipients.disabled = true;
            $(recipients).multiselect('disable');
            $(recipients).multiselect('refresh');

            while (source.firstChild) {
                source.removeChild(source.firstChild);
            }

            source.disabled = true;
            $(source).multiselect('disable');
            $(source).multiselect('refresh');
            source.closest('#source').classList.add('table-hidden');

            while (status.firstChild) {
                status.removeChild(status.firstChild);
            }

            status.disabled = true;
            $(status).multiselect('disable');
            $(status).multiselect('refresh');
            status.closest('#client-status').classList.add('table-hidden');

            while (projects.firstChild) {
                projects.removeChild(projects.firstChild);
            }

            projects.disabled = true;
            $(projects).multiselect('disable');
            $(projects).multiselect('refresh');
            projects.closest('#client-projects').classList.add('table-hidden');
        }
    }
});

goldenvisa.addEventListener('change', function() {
    if (this.value != selectedGoldenvisa) {
        selectedGoldenvisa = this.value;

        if (this.value != '') {
            ajax.ajaxify({
                obj: this,
                method: 'get',
                queue: 'sync',
                action: '{{ Helper::route('api.load-data') }}',
                data: 'method=Recipients&goldenvisa=' + this.value + '&group=' + selectedGroup + '&projects=' + selectedProjects,
                skipErrors: true,
            }).then(function (data) {
                while (recipients.firstChild) {
                    recipients.removeChild(recipients.firstChild);
                }

                $.each(data.data.recipients, function(key, value) {
                    $(recipients).append($('<option></option>').attr('value', value.id).text(value.recipient));
                });

                recipients.disabled = false;
                $(recipients).multiselect('enable');
                $(recipients).multiselect('refresh');
            }).catch(function (error) {
            });
        } else {
            while (recipients.firstChild) {
                recipients.removeChild(recipients.firstChild);
            }

            recipients.disabled = true;
            $(recipients).multiselect('disable');
            $(recipients).multiselect('refresh');
        }
    }
});

$(recipients).multiselect();

$(source).multiselect({
    close: function() {
        if ($(this).val() != selectedSource) {
            selectedSource = $(this).val();

            ajax.ajaxify({
                obj: this,
                method: 'get',
                queue: 'sync',
                action: '{{ Helper::route('api.load-data') }}',
                data: 'method=Recipients&group=' + selectedGroup + '&source=' + $(this).val(),
                skipErrors: true,
            }).then(function (data) {
                while (recipients.firstChild) {
                    recipients.removeChild(recipients.firstChild);
                }

                $.each(data.data.recipients, function(key, value) {
                    $(recipients).append($('<option></option>').attr('value', value.id).text(value.recipient));
                });

                $(recipients).multiselect('refresh');
            }).catch(function (error) {
            });
        }
    },
});

$(status).multiselect({
    close: function() {
        if ($(this).val() != selectedStatus) {
            selectedStatus = $(this).val();

            ajax.ajaxify({
                obj: this,
                method: 'get',
                queue: 'sync',
                action: '{{ Helper::route('api.load-data') }}',
                data: 'method=Recipients&group=' + selectedGroup + '&status=' + $(this).val() + '&projects=' + selectedProjects,
                skipErrors: true,
            }).then(function (data) {
                while (recipients.firstChild) {
                    recipients.removeChild(recipients.firstChild);
                }

                $.each(data.data.recipients, function(key, value) {
                    $(recipients).append($('<option></option>').attr('value', value.id).text(value.recipient));
                });

                $(recipients).multiselect('refresh');
            }).catch(function (error) {
            });
        }
    },
});

$(projects).multiselect({
    close: function() {
        if ($(this).val() != selectedProjects) {
            selectedProjects = $(this).val();

            ajax.ajaxify({
                obj: this,
                method: 'get',
                queue: 'sync',
                action: '{{ Helper::route('api.load-data') }}',
                data: 'method=Recipients&group=' + selectedGroup + (selectedGoldenvisa == '' ? '' : '&goldenvisa=' + selectedGoldenvisa) + '&projects=' + $(this).val() + '&status=' + selectedStatus,
                skipErrors: true,
            }).then(function (data) {
                while (recipients.firstChild) {
                    recipients.removeChild(recipients.firstChild);
                }

                $.each(data.data.recipients, function(key, value) {
                    $(recipients).append($('<option></option>').attr('value', value.id).text(value.recipient));
                });

                $(recipients).multiselect('refresh');
            }).catch(function (error) {
            });
        }
    },
});
