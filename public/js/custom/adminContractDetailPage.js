var app = new Vue({
    el: '#app',
    data: {
        sites: [],
        contracts: [],
        serviceType: 1,
        ownedId: '',
        contractServiceId: '',
        start_date: '',
        end_date: '',
        siteId: ''
    },
    methods: {
        addContractPeriod() {
            axios.get(getSiteDataUrl + '/' + app.ownedId).then(function (response) {
                app.start_date = response.data.contract_start_at
                app.end_date = response.data.contract_end_at
            })
        },
        updateDatePicker() {
            axios.get(getLatestScheduleUrl + '/' + app.contractServiceId).then(function (response) {
                app.serviceType = response.data.service_type;
                if (response.data.service_type == 1) {
                    app.start_date = '';
                    app.end_date = '';
                    $('.schedule-date-picker').datepicker({
                        format: 'yyyy-mm-dd',
                        language: 'ja',
                        startDate: moment(response.data.end_date).add(1, 'days').format('YYYY-MM-DD')
                    });
                } else if (response.data.service_type == 2) {
                    app.siteId = response.data.vtdr_site_id;
                    app.start_date = moment(response.data.start_date).format('YYYY-MM-DD');
                    app.end_date = moment(response.data.end_date).format('YYYY-MM-DD');
                    $('.schedule-date-picker').datepicker({
                        format: 'yyyy-mm-dd',
                        language: 'ja',
                    });
                }
            })
        },
        fetchData() {
            axios.get(getContractScheduleUrl).then(function (response) {
                app.contracts = response.data
            }).catch(function (error) {
                app.errorMsg = 'Error! Could not reach the API. ' + error
            });
        },
        changeDateFormat(date) {
            return moment(date).format('YYYY-MM-DD');
        },
        setServiceTypeLabel(type) {
            switch (type) {
                case 1:
                    return "AD";
                case 2:
                    return "OWNED";
                case 3:
                    return "Post";
                default:
                    return "";
            }
        },
        updateContract() {
            axios.post(updateContractUrl, this.contract).then(function (response) {
                debugger
            }).catch(function (error) {
                debugger
                app.errorMsg = 'Error! Could not reach the API. ' + error
            });
        }
    },
    mounted() {
        this.fetchData();

        axios.get(syncOwnedContractUrl).then(function (response) {
            if (response.data == 'synced') {
                app.fetchData();
            }
        }).catch(function (error) {
            app.errorMsg = 'Error! Could not reach the API. ' + error
        });

        axios.get(getAllSiteUrl).then(function (response) {
            app.sites = response.data
        }).catch(function (error) {
            app.errorMsg = 'Error! Could not reach the API. ' + error
        });
    }
});

$(document).ready(function () {
    $('#update_contract').on('shown.bs.modal', function (e) {
        app.serviceType = '';
        app.start_date = '';
        app.end_date = '';
    });

    $('#add_contract').on('shown.bs.modal', function (e) {
        app.serviceType = '';
        app.start_date = '';
        app.end_date = '';
    });
});