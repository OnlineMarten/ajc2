<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Example Component</div>

                    <div class="card-body">

                            <VueMiniCalendar v-model="date" :config="config"></VueMiniCalendar>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import vueMiniCalendar from 'vue-mini-calendar';
    export default {
		components: {
			vueMiniCalendar,
		},
		data() {
			return {
				date: new Date(),
				config: {
					mode: 'single', // multiple, single, range, none
					rangeSeparator: ' — ',
					multipleSeparator: ', ',
					format: 'MM/dd/yyyy',
					firstDayOfWeek: 1, // 1 = Sunday ... 7 = Monday
					weekDays: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
					months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
					show: 'calendar', // input, inline, calendar
				}
			};
		}
	};
</script>
