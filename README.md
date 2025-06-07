# unraid-tailscale-utils

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](LICENSE)
[![GitHub Releases](https://img.shields.io/github/v/release/unraid/unraid-tailscale-utils)](https://github.com/unraid/unraid-tailscale-utils/releases)
[![Last Commit](https://img.shields.io/github/last-commit/unraid/unraid-tailscale-utils)](https://github.com/unraid/unraid-tailscale-utils/commits/main/)
[![Code Style: PHP-CS-Fixer](https://img.shields.io/badge/code%20style-php--cs--fixer-brightgreen.svg)](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
![GitHub Downloads (all assets, all releases)](https://img.shields.io/github/downloads/unraid/unraid-tailscale-utils/total)
![GitHub Downloads (all assets, latest release)](https://img.shields.io/github/downloads/unraid/unraid-tailscale-utils/latest/total)

## Features
- Easy Tailscale installation and management on Unraid
- Helper scripts for authentication and status
- Example configurations for common use cases

## Development

### Requirements

- [Composer](https://getcomposer.org/) for dependency management

### Testing

1. Clone the repository.
2. Run `./composer install` to install dependencies.
3. For local testing, copy the contents of `src/` (except for the `install` directory) to the root of the Unraid test system.

### Release

`.github/workflows/release.yml` Automatically builds a Slackware package when a Github release is created. The resulting package is installed on Unraid via the [unraid/unraid-tailscale](https://github.com/unraid/unraid-tailscale) plugin.

### Contributing
Pull requests and issues are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for contribution guidelines, including code checks, commit message conventions, and licensing. You can also open an issue to discuss your idea.

## License
This project is licensed under the GNU General Public License v3.0 or later. See [LICENSE](LICENSE) for details.