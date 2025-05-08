<template>
    <Line :data="chartData" :options="options" />
</template>

<script setup>
import { Line } from 'vue-chartjs'
import {
Chart as ChartJS,
Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement)

defineProps(['chartData'])

const options = {
responsive: true,
plugins: {
    legend: { position: 'top' },
},
}
</script>
