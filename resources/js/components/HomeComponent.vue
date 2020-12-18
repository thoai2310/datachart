<template>
  <div class="small">
    <bar-chart :chart-data="dataFirst" :options="options"></bar-chart>
    <bar-chart :chart-data="dataSecond" :options="options"></bar-chart>
  </div>
</template>

<script>
import BarChart from './BarChart.js';
import preUrl from '../config/config.js'

export default {
  components: {
    BarChart,
    preUrl
  },
  data() {
    return {
      dataFirst: {},
      options: {},
      dataSecond : {}
    }
  },
  mounted() {
    this.fillData()
  },
  methods: {
    fillData() {
      let uri = preUrl + 'getDataChart';
      this.axios.get(uri).then(response => {
        this.dataFirst = response.data.dataFirst;
        this.dataSecond = response.data.dataSecond;
        console.log(this.dataFirst);
        this.dataFirst = {
          labels: this.dataFirst.labels,
          datasets: [
            {
              label: 'My First dataset',
              backgroundColor: '#f87979',
              data: this.dataFirst.data
            }
          ]
        };
        this.dataSecond = {
          labels: this.dataSecond.labels,
          datasets: [
            {
              label: 'My Second dataset',
              backgroundColor: '#f87979',
              data: this.dataSecond.data
            }
          ]
        };
      });
      this.options = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          xAxes: [{
            ticks: {
              min: 0
            }
          }],
          yAxes: [{
            stacked: true
          }]
        }
      }
    }
  }
}
</script>

<style>
.small {
  max-width: 600px;
  margin: 150px auto;
}
</style>
