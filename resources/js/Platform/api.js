import axios from "axios";

const client = axios.create({
    headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest"
    }
});

client.interceptors.request.use(config => {
    config.headers["X-CSRF-TOKEN"] = document.querySelector('meta[name="csrf-token"]')?.content || "";
    return config;
});

export function errorMessage(error, fallback = "No fue posible completar la operación.") {
    const response = error?.response?.data;
    const validation = response?.errors ? Object.values(response.errors).flat()[0] : null;

    return validation || response?.message || fallback;
}

export default client;
