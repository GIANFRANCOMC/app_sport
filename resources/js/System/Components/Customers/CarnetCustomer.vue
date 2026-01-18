<template>
    <div>
        <div class="overflow-auto">
            <div id="carnet" ref="carnet" class="card carnet-card-entity mx-auto shadow-sm border border-light" style="min-width: 400px;" :style="{ backgroundImage: backgroundUrl }">
                <img :src="fallbackUrl" class="carnet-watermark-entity img-fluid" style="width: 25%;" @error="onImageError($event)"/>
                <div class="card-header d-flex align-items-center justify-content-start carnet-header-entity pb-0 pt-3">
                    <span class="text-white fw-semibold h5" v-text="header"></span>
                </div>
                <div class="card-body d-flex flex-column align-items-center py-3">
                    <template v-if="isLoading">
                        <Loader/>
                    </template>
                    <template v-else>
                        <div class="carnet-qr-entity flex-shrink-0 p-2 bg-white rounded-4 shadow-sm">
                            <img :src="qrImage" alt="QR" class="img-fluid" style="width: 320px; height: 320px;"/>
                        </div>
                        <div class="mt-3 text-center text-dark text-shadow-dark-1">
                            <p class="h6 mb-1 fw-bold">
                                <i class="fa fa-user"></i>
                                <span class="ms-2" v-text="title"></span>
                            </p>
                            <p class="h6 mb-0 fw-semibold text-opacity-75" v-text="subtitle"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-center gap-2 gap-md-3 mt-3">
            <button @click="compartirCarnet" class="btn btn-primary waves-effect">
                <i class="fa-solid fa-share-nodes"></i>
                <span class="ms-2">Compartir</span>
            </button>
            <button @click="descargarCarnet" class="btn btn-success waves-effect">
                <i class="fa fa-download"></i>
                <span class="ms-2">Descargar</span>
            </button>
        </div>
    </div>
</template>

<script>
import QRCodeStyling from "qr-code-styling";
import html2canvas from "html2canvas";

import { encodeBase64UTF8 } from "../../Helpers/Utils.js";

export default {
    name: "CarnetCustomer",
    props: {
        customer: {
            type: Object,
            required: false
        }
    },
    data() {
        return {
            qrImage: "",
            isLoading: true
        };
    },
    computed: {
        header() {

            return window?.company?.commercial_name ?? "";

        },
        title() {

            return this.customer?.name ?? "";

        },
        subtitle() {

            return `N° ${this.customer?.document_number ?? ""}`;

        },
        backgroundUrl() {

            return `url('${"/System/assets/img/utils/customers/carnet/2.png"}')`;

        },
        fallbackUrl() {

            return window.ownerApp.assets.img.logotype;

        }
    },
    mounted() {

        this.generarQR();

    },
    methods: {
        onImageError(event) {

            event.target.src = this.fallbackUrl;

        },
        generarQR() {

            if(this.customer?.id) {

                this.isLoading = true;

                const dataJson = {
                    bp: encodeBase64UTF8(JSON.stringify({id: this.customer?.id ?? ""})),
                    identificator: this.customer?.document_number ?? "",
                    name: this.customer?.name ?? ""
                };

                const logotype = window.company?.logotype;

                const qrConfig = {
                    width: 2000,
                    height: 2000,
                    data: JSON.stringify(dataJson),
                    dotsOptions: {
                        color: "#000000",
                        type: "rounded"
                    },
                    backgroundOptions: {
                        color: "#ffffff"
                    }
                };

                // Solo agregar imagen si existe el logotipo
                if(logotype) {

                    qrConfig.image = "/storage/" + logotype;
                    qrConfig.imageOptions = {
                        crossOrigin: "anonymous",
                        imageSize: 0.30
                    };

                }

                const qr = new QRCodeStyling(qrConfig);

                qr.getRawData("png")
                .then((blob) => {

                    this.qrImage = URL.createObjectURL(blob);

                    this.isLoading = false;

                })
                .catch((error) => {

                    console.error("Error al generar QR:", error);
                    this.isLoading = false;

                });

            }

        },
        async compartirCarnet() {

            const original = this.$refs.carnet;
            const cloned = original.cloneNode(true);

            document.body.appendChild(cloned);

            // 2. Esperar a que todas las <img> (logo + QR) estén cargadas
            const imgs = Array.from(cloned.querySelectorAll('img'));
            await Promise.all(imgs.map(img => img.complete ? Promise.resolve() : new Promise(res => { img.onload = img.onerror = res; })));

            // 3. Renderizar con html2canvas a mayor resolución
            const canvas = await html2canvas(cloned, {
                scale: 2,
                useCORS: true,
                allowTaint: false,
                backgroundColor: null
            });

            // 4. Eliminar el clon
            document.body.removeChild(cloned);

            // 5. Compartir
            canvas.toBlob(async blob => {

                const file = new File([blob], 'carnet.png', { type: 'image/png' });

                if(navigator.canShare && navigator.canShare({ files: [file] })) {

                    try {

                        await navigator.share({title: 'Carnet Digital', text: this.customer?.name, files: [file]});

                    }catch(err) {

                        console.error('Error al compartir:', err);

                    }

                }else {

                    alert('Tu navegador no soporta compartir archivos.');
                }

            }, 'image/png');

        },
        async descargarCarnet() {

            const original = this.$refs.carnet;
            const cloned = original.cloneNode(true);

            document.body.appendChild(cloned);

            const imgs = Array.from(cloned.querySelectorAll("img"));

            await Promise.all(imgs.map(img => img.complete ? Promise.resolve() : new Promise(res => { img.onload = img.onerror = res; })));

            const canvas = await html2canvas(cloned, {
                scale: 2,
                useCORS: true,
                allowTaint: false,
                backgroundColor: null
            });

            document.body.removeChild(cloned);

            const link = document.createElement("a");
            link.href = canvas.toDataURL("image/png");
            link.download = `carnet_${this.customer?.document_number}.png`;
            link.click();

        }
    },
    watch: {
        customer: {
            deep: true,
            handler() {

                this.generarQR();

            }
        }
    }
};
</script>

<style scoped>
.carnet-card-entity {
    max-width: 300px;
    min-width: 300px;
    background-color: #ffffff;
    border-radius: 1.5rem;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    background-size: cover;
    background-repeat: no-repeat;
}

.carnet-header-entity {
    background: linear-gradient(135deg, #2899E5, #1A1A35);
}

.carnet-qr-entity {
    border: 3px solid #a19ca9;
    border-radius: 1rem;
    box-shadow: inset 0 0 8px rgba(157, 140, 140, 0.05);
    backdrop-filter: blur(2px);
}

.carnet-watermark-entity {
    position: absolute;
    top: 15px;
    right: 10px;
    z-index: 5;
    pointer-events: none;
}
</style>
