<template>
    <button :class="`btn btn-${type}`" :disabled="disabled">
        <slot />
    </button>
</template>

<script>
export default {
    props: {
        type: {
            type: String,
            default: 'primary',
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    },
};
</script>
