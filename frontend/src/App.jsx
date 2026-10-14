import { useEffect, useMemo, useState } from "react";
import { getMenu, placeOrder, getOrder } from "./services/api";
import "./App.css";

const money = (value) =>
  new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 2,
  }).format(Number(value));

function App() {
  const [menu, setMenu] = useState([]);
  const [cart, setCart] = useState({});
  const [customer, setCustomer] = useState({
    customer_name: "",
    address: "",
    phone: "",
  });
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    getMenu()
      .then(setMenu)
      .catch(() => setError("Unable to load menu. Please try again."))
      .finally(() => setLoading(false));
  }, []);

  const cartItems = useMemo(
    () =>
      menu
        .filter((item) => cart[item.id] > 0)
        .map((item) => ({
          ...item,
          quantity: cart[item.id],
        })),
    [menu, cart]
  );

  const total = cartItems.reduce(
    (sum, item) => sum + Number(item.price) * item.quantity,
    0
  );

  const changeQuantity = (id, amount) => {
    setCart((previous) => {
      const next = { ...previous };
      const quantity = (next[id] || 0) + amount;

      if (quantity <= 0) {
        delete next[id];
      } else {
        next[id] = Math.min(quantity, 99);
      }

      return next;
    });
  };

  const updateCustomer = (event) => {
    setCustomer({
      ...customer,
      [event.target.name]: event.target.value,
    });
  };

  const checkout = async (event) => {
    event.preventDefault();
    setError("");

    if (cartItems.length === 0) {
      setError("Please add at least one item to your cart.");
      return;
    }

    setSubmitting(true);

    try {
      const created = await placeOrder({
        ...customer,
        items: cartItems.map((item) => ({
          menu_item_id: item.id,
          quantity: item.quantity,
        })),
      });

      setOrder(created);
      setCart({});
    } catch (err) {
      const validationErrors = err.response?.data?.errors;
      setError(
        validationErrors
          ? Object.values(validationErrors).flat().join(" ")
          : "Unable to place your order. Please try again."
      );
    } finally {
      setSubmitting(false);
    }
  };

  useEffect(() => {
    if (!order || ["delivered", "cancelled"].includes(order.status)) {
      return;
    }

    const timer = window.setInterval(async () => {
      try {
        setOrder(await getOrder(order.id));
      } catch {
        // Retain the last successfully retrieved order status.
      }
    }, 3000);

    return () => window.clearInterval(timer);
  }, [order?.id, order?.status]);

  if (loading) return <main className="container">Loading menu...</main>;

  return (
    <main className="container">
      <header className="hero">
        <p className="eyebrow">FRESH · FAST · DELICIOUS</p>
        <h1>QuickBite</h1>
        <p>Your favourite food, delivered to your door.</p>
      </header>

      {error && (
        <div className="error" role="alert">
          {error}
        </div>
      )}

      {order ? (
        <section className="panel">
          <h2>Order #{order.id}</h2>
          <p>Thank you, {order.customer_name}!</p>
          <p>
            Status: <strong>{order.status.replaceAll("_", " ")}</strong>
          </p>
          <p>Total: {money(order.total_amount)}</p>
          <p className="muted">
            This page checks the server for status updates every 3 seconds.
          </p>
          <button onClick={() => setOrder(null)}>
            Back to menu
          </button>
        </section>
      ) : (
        <>
          <section>
            <h2>Our Menu</h2>

            <div className="menu-grid">
              {menu.map((item) => (
                <article className="food-card" key={item.id}>
                  {item.image_url && (
                    <img
                      src={item.image_url}
                      alt={item.name}
                      loading="lazy"
                    />
                  )}

                  <div className="food-content">
                    <h3>{item.name}</h3>
                    <p>{item.description}</p>
                    <div className="food-footer">
                      <strong>{money(item.price)}</strong>
                      <button
                        onClick={() => changeQuantity(item.id, 1)}
                      >
                        Add to cart
                      </button>
                    </div>

                    {cart[item.id] > 0 && (
                      <div className="quantity">
                        <button
                          aria-label={`Decrease ${item.name}`}
                          onClick={() => changeQuantity(item.id, -1)}
                        >
                          −
                        </button>
                        <span>{cart[item.id]}</span>
                        <button
                          aria-label={`Increase ${item.name}`}
                          onClick={() => changeQuantity(item.id, 1)}
                        >
                          +
                        </button>
                      </div>
                    )}
                  </div>
                </article>
              ))}
            </div>
          </section>

          <section className="panel">
            <h2>Your Cart</h2>

            {cartItems.length === 0 ? (
              <p>Your cart is empty.</p>
            ) : (
              <>
                {cartItems.map((item) => (
                  <div className="cart-row" key={item.id}>
                    <span>
                      {item.name} × {item.quantity}
                    </span>
                    <strong>
                      {money(Number(item.price) * item.quantity)}
                    </strong>
                  </div>
                ))}

                <div className="cart-total">
                  <span>Total</span>
                  <strong>{money(total)}</strong>
                </div>

                <h3>Delivery Details</h3>

                <form onSubmit={checkout}>
                  <label>
                    Full name
                    <input
                      name="customer_name"
                      value={customer.customer_name}
                      onChange={updateCustomer}
                      minLength={2}
                      maxLength={100}
                      required
                    />
                  </label>

                  <label>
                    Delivery address
                    <textarea
                      name="address"
                      value={customer.address}
                      onChange={updateCustomer}
                      minLength={10}
                      maxLength={1000}
                      required
                    />
                  </label>

                  <label>
                    Phone number
                    <input
                      name="phone"
                      type="tel"
                      value={customer.phone}
                      onChange={updateCustomer}
                      pattern="[+]?[0-9 ()-]{7,20}"
                      required
                    />
                  </label>

                  <button
                    type="submit"
                    disabled={submitting || cartItems.length === 0}
                  >
                    {submitting ? "Placing order..." : "Place order"}
                  </button>
                </form>
              </>
            )}
          </section>
        </>
      )}
    </main>
  );
}

export default App;