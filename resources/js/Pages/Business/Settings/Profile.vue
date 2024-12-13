<template>
    <SettingsLayout :business="business">
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div>
                        <InputLabel for="name" value="Business Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Description" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            class="textarea textarea-bordered w-full h-24 mt-1"
                            placeholder="Describe your business..."
                        />
                        <InputError :message="errors.description" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="street_address" value="Street Address" />
                        <TextInput
                            id="street_address"
                            v-model="form.street_address"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="errors.street_address" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="city" value="City" />
                        <TextInput
                            id="city"
                            v-model="form.city"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="errors.city" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="postal_code" value="Postal Code" />
                        <TextInput
                            id="postal_code"
                            v-model="form.postal_code"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="errors.postal_code" class="mt-2" />
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <div>
                        <InputLabel for="nip" value="NIP (Tax ID)" />
                        <TextInput
                            id="nip"
                            v-model="form.nip"
                            type="text"
                            maxlength="10"
                            class="mt-1 block w-full"
                            placeholder="0000000000"
                        />
                        <InputError :message="errors.nip" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Phone" />
                        <TextInput
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="errors.phone" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="errors.email" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="website" value="Website" />
                        <TextInput
                            id="website"
                            v-model="form.website"
                            type="url"
                            class="mt-1 block w-full"
                            placeholder="https://"
                        />
                        <InputError :message="errors.website" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Business Logo" />
                        <div class="mt-2">
                            <label class="block">
                                <span class="sr-only">Choose logo</span>
                                <input
                                    type="file"
                                    class="block w-full text-sm text-base-content
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-full file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-primary file:text-white
                                        hover:file:bg-primary/90"
                                    accept="image/*"
                                    @change="handleLogoUpload"
                                />
                            </label>
                        </div>
                        <InputError :message="errors.logo" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Cover Image" />
                        <div class="mt-2">
                            <label class="block">
                                <span class="sr-only">Choose cover image</span>
                                <input
                                    type="file"
                                    class="block w-full text-sm text-base-content
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-full file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-primary file:text-white
                                        hover:file:bg-primary/90"
                                    accept="image/*"
                                    @change="handleCoverImageUpload"
                                />
                            </label>
                        </div>
                        <InputError :message="errors.cover_image" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="mt-6 flex justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                    @click="updateProfile"
                >
                    <span v-if="processing">Saving...</span>
                    <span v-else>Save Changes</span>
                </PrimaryButton>
            </div>
        </div>
    </SettingsLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    business: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const processing = ref(false);

const form = ref({
    name: props.business.name || '',
    description: props.business.description || '',
    street_address: props.business.street_address || '',
    city: props.business.city || '',
    postal_code: props.business.postal_code || '',
    nip: props.business.nip || '',
    phone: props.business.phone || '',
    email: props.business.email || '',
    website: props.business.website || '',
    logo: null,
    cover_image: null,
});

const handleLogoUpload = (e) => {
    const file = e.target.files[0];
    form.value.logo = file;
};

const handleCoverImageUpload = (e) => {
    const file = e.target.files[0];
    form.value.cover_image = file;
};

const updateProfile = () => {
    processing.value = true;

    router.post(route('business.settings.profile.update'), {
        _method: 'PUT',
        ...form.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};</script> 