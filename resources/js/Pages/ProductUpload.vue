<template>
    <div>
        <h2>Upload Product Photo</h2>
        <form @submit.prevent="uploadPhoto">
            <input type="file" @change="onFileChange" accept="image/*" required />
            <input v-model="product_name" type="text" placeholder="Product name" required />
            <input v-model="brand" type="text" placeholder="Brand (optional)" />
            <input v-model="category" type="text" placeholder="Category (optional)" />
            <input v-model.number="price" type="number" step="0.01" placeholder="Price (optional)" />
            <input v-model="sku" type="text" placeholder="SKU (optional)" />
            <input v-model="description" type="text" placeholder="Product description (optional)" />
            <button type="submit">Upload</button>
        </form>

        <div v-if="generatedImages.length">
            <h3>Generated Product Cards</h3>
            <div v-for="(img, index) in generatedImages" :key="index">
                <img :src="img" alt="Generated product card" width="300" />
            </div>
        </div>

        <div v-if="productCard">
            <h3>Generated Marketplace Data</h3>
            <pre>{{ productCard }}</pre>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            file: null,
            product_name: '',
            brand: '',
            category: '',
            price: null,
            sku: '',
            description: '',
            generatedImages: [],
            productCard: null,
        };
    },
    methods: {
        onFileChange(e) {
            this.file = e.target.files[0];
        },
        async uploadPhoto() {
            if (!this.file || !this.product_name) return alert('Please fill required fields.');

            let formData = new FormData();
            formData.append('photo', this.file);
            formData.append('product_name', this.product_name);
            if (this.brand) formData.append('brand', this.brand);
            if (this.category) formData.append('category', this.category);
            if (this.price !== null && this.price !== '') formData.append('price', this.price);
            if (this.sku) formData.append('sku', this.sku);
            if (this.description) formData.append('description', this.description);

            try {
                const res = await axios.post('/api/product/upload', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });

                this.generatedImages = res.data.generated_images;
                this.productCard = res.data.product_card;
            } catch (error) {
                alert('Error uploading photo.');
                console.error(error);
            }
        },
    },
};
</script>
