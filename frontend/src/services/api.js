import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || "http://localhost:8000/api",
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
});

export const getMenu = async () => {
  const response = await api.get("/menu");
  return response.data.data;
};

export const placeOrder = async (order) => {
  const response = await api.post("/orders", order);
  return response.data.data;
};

export const getOrder = async (id) => {
  const response = await api.get(`/orders/${id}`);
  return response.data.data;
};

export const getOrders = async () => {
  const response = await api.get("/orders");
  return response.data.data;
};

export default api;