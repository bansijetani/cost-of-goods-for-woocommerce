# 📦 Cost of Goods for WooCommerce

**Version:** 1.0.0  
**Author:** Bansi Jetani  
**License:** GPLv2 or later  
**Requires at least:** WordPress 5.0  
**Tested up to:** WordPress 6.x  
**Requires PHP:** 7.4+  
**WooCommerce Compatible**

---

## 📝 Description

The **Cost of Goods for WooCommerce** plugin adds the ability to manage product purchase costs (COGS) in your WooCommerce store. By associating cost prices with each product, this plugin enables more accurate profit margin calculations and provides a better understanding of your store's financial performance.

Designed for flexibility and ease of use, the plugin integrates seamlessly with WooCommerce product settings and is built using clean, maintainable PHP code.

---

## 🚀 Features

- Add purchase cost (COGS) field to all WooCommerce products.
- Track cost values directly from the product admin page.
- Structure prepared for order-level cost integration and profit tracking.
- Modular, object-oriented architecture for easy customization.
- Lightweight and WooCommerce-native with no external dependencies.

---

## 🛠️ Installation

1. Upload the plugin to your `/wp-content/plugins/` directory.
2. Activate it through the WordPress admin panel: **Plugins > Installed Plugins > Activate**.
3. Ensure WooCommerce is installed and active.
4. Navigate to any WooCommerce product to find the **Cost Price** input field.

---

## 🧱 Plugin Structure

- `cost-of-goods-for-woocommerce.php` — Main plugin file
- `includes/class-wc-cog-core-functions.php` — Core setup and plugin control
- `includes/class-wc-cog-inputs.php` — Admin UI for cost fields
- `includes/class-wc-cog-products.php` — Product cost data handling
- `includes/class-wc-cog-orders.php` — (Extendable) Order-level cost logic

---

## 🔒 License

This plugin is licensed under the GPLv2 or later.

---

## 🤝 Contribute

Pull requests, issues, and feature suggestions are welcome!  
Feel free to fork the repository and submit improvements.

---

## 🙋 Support

For issues or questions, please open an issue in the [GitHub repository](#) or contact the plugin author.

---

## 💡 Roadmap

- Cost reporting and analytics
- Order-level profit calculation
- Export tools for cost and profit tracking
- Multi-currency support

