<template>
    <div>
        <h2>Upload Product Photo</h2>
        <form @submit.prevent="uploadPhoto">
            <input type="file" @change="onFileChange" accept="image/*" required />
            <input v-model="description" type="text" placeholder="Product description" required />
            <button type="submit">Upload</button>
        </form>

        <div v-if="generatedImages.length">
            <h3>Generated Product Cards</h3>
            <div v-for="(img, index) in generatedImages" :key="index">
                <img :src="img" alt="Generated product card" width="300" />
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            file: null,
            description: '',
            generatedImages: [],
        };
    },
    methods: {
        onFileChange(e) {
            this.file = e.target.files[0];
        },
        async uploadPhoto() {
            if (!this.file || !this.description) return alert('Please fill all fields.');

            let formData = new FormData();
            formData.append('photo', this.file);
            formData.append('description', this.description);

            try {
                const res = await axios.post('/api/product/upload', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });

                this.generatedImages = res.data.generated_images;
            } catch (error) {
                alert('Error uploading photo.');
                console.error(error);
            }
        },
    },
};
</script>
