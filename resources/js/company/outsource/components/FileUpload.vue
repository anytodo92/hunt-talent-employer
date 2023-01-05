<template>
    <input type="file" @change="selectFile">
</template>

<script>

export default {
    data: () => ({
        photo: null,
        description: '',
        productId: 0,
    }),

    methods: {
        selectFile(event) {
            // 'files' is always an array because the file input may be in multiple mode
            this.photo = event.target.files[0];

            const data = new FormData();
            data.append('photo', this.photo);
            data.append('description', this.description);
            data.append('productId', this.productId);
            axios.post("/api/photo", data);
        }
    }
}
</script>
