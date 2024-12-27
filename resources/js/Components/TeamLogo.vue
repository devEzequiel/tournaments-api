<template>
    <div class="team-logo-wrapper">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 100 120"
            class="shield"
        >
            <path
                d="M50,0 L10,60 L50,120 L90,60 Z"
                stroke="black"
                stroke-width="2"
                fill="none"
            />
            <path
                :fill="firstColor"
                d="M50,0 L10,60 L50,120 Z"
            />
            <path
                :fill="secondColor"
                d="M50,0 L90,60 L50,120 Z"
            />
        </svg>
    </div>
</template>

<script>
export default {
    props: {
        firstColor: {
            type: String,
            required: true,
        },
        secondColor: {
            type: String,
            required: true,
        },
    },
};
</script>

<style scoped>
.team-logo-wrapper {
    width: 40px;
    height: auto;
    display: flex;
    justify-content: center;
    align-items: center;
}

.shield {
    width: 40px;
    height: auto;
}
</style>
