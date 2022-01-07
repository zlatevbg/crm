var group = document.querySelector('#input-group');
var projects = document.querySelector('#input-projects');
var status = document.querySelector('#input-status');
var recipients = document.querySelector('#input-recipients');
var numbers = document.querySelector('#input-numbers');
var selectedGroup = '{{ $api->model->group ?: '' }}';
var selectedStatus = @json($api->model->status);
var selectedProjects = @json($api->model->projects);

group.addEventListener('change', function() {
    if (parseInt(this.value) != selectedGroup) {
        selectedGroup = this.value;

        if (this.value) {
            if (selectedGroup == 'custom') {
                numbers.disabled = false;
                numbers.value = '';
                numbers.closest('.form-group').classList.remove('table-hidden');

                while (recipients.firstChild) {
                    recipients.removeChild(recipients.firstChild);
                }

                recipients.disabled = true;
                $(recipients).multiselect('disable');
                $(recipients).multiselect('refresh');
                recipients.closest('.form-group').classList.add('table-hidden');

                while (status.firstChild) {
                    status.removeChild(status.firstChild);
                }

                status.disabled = true;
                $(status).multiselect('disable');
                $(status).multiselect('refresh');
                status.closest('.form-group').classList.add('table-hidden');

                while (projects.firstChild) {
                    projects.removeChild(projects.firstChild);
                }

                projects.disabled = true;
                $(projects).multiselect('disable');
                $(projects).multiselect('refresh');
                projects.closest('.form-group').classList.add('table-hidden');
            } else {
                numbers.disabled = true;
                numbers.value = '';
                numbers.closest('.form-group').classList.add('table-hidden');

                ajax.ajaxify({
                    obj: this,
                    method: 'get',
                    queue: 'sync',
                    action: '{{ Helper::route('api.load-data') }}',
                    data: 'method=SmsRecipients&group=' + this.value + '&include-status&include-projects',
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
                    recipients.closest('.form-group').classList.remove('table-hidden');

                    if (data.data.status) {
                        status.closest('.form-group').classList.remove('table-hidden');

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
                        status.closest('.form-group').classList.add('table-hidden');
                    }

                    if (data.data.projects) {
                        projects.closest('.form-group').classList.remove('table-hidden');

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
                        projects.closest('.form-group').classList.add('table-hidden');
                    }
                }).catch(function (error) {
                });
            }
        } else {
            while (recipients.firstChild) {
                recipients.removeChild(recipients.firstChild);
            }

            recipients.disabled = true;
            $(recipients).multiselect('disable');
            $(recipients).multiselect('refresh');
            recipients.closest('.form-group').classList.add('table-hidden');

            while (status.firstChild) {
                status.removeChild(status.firstChild);
            }

            status.disabled = true;
            $(status).multiselect('disable');
            $(status).multiselect('refresh');
            status.closest('.form-group').classList.add('table-hidden');

            while (projects.firstChild) {
                projects.removeChild(projects.firstChild);
            }

            projects.disabled = true;
            $(projects).multiselect('disable');
            $(projects).multiselect('refresh');
            projects.closest('.form-group').classList.add('table-hidden');

            numbers.disabled = true;
            numbers.value = '';
            numbers.closest('.form-group').classList.add('table-hidden');
        }
    }
});

$(recipients).multiselect();

$(status).multiselect({
    close: function() {
        if ($(this).val() != selectedStatus) {
            selectedStatus = $(this).val();

            ajax.ajaxify({
                obj: this,
                method: 'get',
                queue: 'sync',
                action: '{{ Helper::route('api.load-data') }}',
                data: 'method=SmsRecipients&group=' + selectedGroup + '&status=' + $(this).val() + '&projects=' + selectedProjects,
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
                data: 'method=SmsRecipients&group=' + selectedGroup + '&projects=' + $(this).val() + '&status=' + selectedStatus,
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

splitSms.initialize()
