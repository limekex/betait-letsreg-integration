# Betait LetsReg Events Integration


## Overview

**Betait LetsReg Events Integration** aim to become a powerful WordPress plugin designed to seamlessly integrate with the LetsReg API, allowing you to fetch, display, and manage events directly from your WordPress dashboard. Whether you're organizing workshops, seminars, or any other type of event, this plugin provides a user-friendly interface to keep your event listings up-to-date and interactive.

> **Note:** This project is currently under development. Contributions are welcome! Please see the [Contributing](#contributing) section below for more details.

## Features

- **API Integration:** Connects with the LetsReg API to fetch events data in real-time.
- **Dynamic Event Table:** Displays events in a sortable and filterable table within the WordPress admin area.
- **AJAX-Based Loading:** Load more events without refreshing the page, ensuring a smooth user experience.
- **Event Management:** Easily add events to your WordPress site with a single click.
- **Search Functionality:** Quickly find events using the built-in search bar.
- **Sorting Indicators:** Visual double arrows indicate sortable columns and their current sorting direction.
- **Debug Logging:** Comprehensive logging for easy troubleshooting and debugging.
- **Security:** Utilizes WordPress nonces and capability checks to ensure secure operations.

## Installation

1. **Download the Plugin:**
   - Clone the repository or download the ZIP file from GitHub.

2. **Upload to WordPress:**
   - Navigate to `Plugins > Add New` in your WordPress dashboard.
   - Click on `Upload Plugin` and select the downloaded ZIP file.
   - Alternatively, unzip the folder and upload it via FTP to the `/wp-content/plugins/` directory.

3. **Activate the Plugin:**
   - After uploading, go to `Plugins > Installed Plugins`.
   - Find **Betait LetsReg Events Manager** and click `Activate`.

## Configuration

1. **API Credentials:**
   - After activation, navigate to `Settings > Betait LetsReg`.
   - Enter your LetsReg API credentials, including the **Base URL** and **Access Token**.
   - Save the settings.

2. **Organizer ID:**
   - Specify your **Organizer ID** to fetch events related to your organization.
   - This can also be set in the settings page.

3. **Advanced Settings (Optional):**
   - Toggle advanced options as needed.
   - Customize sorting fields and directions according to your preferences.

## Usage

1. **Accessing Events:**
   - Go to `Betait LetsReg > Events` in your WordPress dashboard.
   - The plugin will automatically fetch and display events from the LetsReg API.

2. **Sorting Events:**
   - Click on any sortable column header to sort the events.
   - Visual double arrows indicate that the column is sortable.
   - The active sorting direction is highlighted for clarity.

3. **Loading More Events:**
   - Click the `Load More` button at the bottom of the table to fetch additional events via AJAX.

4. **Adding Events to WordPress:**
   - Click the `+` button next to an event to add it directly to your WordPress site.
   - Confirmation messages will notify you of successful additions.

5. **Searching Events:**
   - Use the search bar above the table to filter events by name in real-time.

## Development

### Project Status

This project is actively under development. New features, improvements, and bug fixes are being continuously added. Contributions from the community are highly encouraged!

### Contributing

Contributions are welcome! Please follow these steps to contribute:

1. **Fork the Repository**

2. **Create a Feature Branch**
    git checkout -b feature/YourFeature

3. **Your Changes**
    git commit -m "Add some feature"

4. **Push to the Branch**
    git push origin feature/YourFeature

5. **Open a Pull Request**

Please ensure your code adheres to the project's coding standards and includes appropriate tests where applicable.

### Setting Up Locally
1. **Clone the Repository:**
git clone https://github.com/yourusername/betait-letsreg-events-manager.git

2. **Install Dependencies:**
Ensure you have WordPress set up locally.
Place the plugin folder in the /wp-content/plugins/ directory.

3. **Activate the Plugin:**
Activate the plugin through the WordPress dashboard.

## Reporting Issues
If you encounter any issues or have suggestions for improvements, please open an issue on the GitHub Issues page.

## Changelog
1.0.0
Initial release.
API integration with LetsReg.
Dynamic, sortable, and filterable event table.
AJAX-based loading of additional events.
Event management and addition to WordPress.
Search functionality.
Debug logging for troubleshooting.
Security enhancements with nonces and capability checks.

## License
This plugin is licensed under the GNU General Public License v3.0.

## Support
For support, please open an issue on the GitHub Issues page or contact the plugin author directly.

Developed by Betait Solutions
Visit our website at https://betait.no