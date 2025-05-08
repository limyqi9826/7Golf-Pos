<template>
    <Doughnut :data="chartData" :options="options" />
</template>

<script setup>
import { Doughnut } from 'vue-chartjs'
import {
Chart as ChartJS,
Title, Tooltip, Legend, ArcElement
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, ArcElement)

defineProps(['chartData'])

const options = {
responsive: true,
plugins: {
    legend: { position: 'bottom' },
},
}
</script>
