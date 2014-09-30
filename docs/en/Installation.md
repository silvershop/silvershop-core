# Installation

For general setup instructions, see the README.md file.

## Configuration Options

The example_config.php file gives an exaustive list of the possible configuration options within the shop module.

## Testing / Development Environment

Please note the tools accessabile via [yoursite]/dev/shop.

### Debugging

If you are wanting to use a debugger tool, you'll probably need to make sure you have an index.php file, which can be found in the
SilverStripe installer. Point your debugger to use index.php, as it likley wont be able to handle using htaccess configurations.

### EMails

To catch local emails, you either need to set up a local dummy SMTP server, or...

 * Windows - you can run the "Antix SMTP Server For Developers", and open the emails in your preferred email client.
 * Linux,Mac - pipe emails to a custom php script, such as [this one](http://blogs.bigfish.tv/adam/2009/12/03/setup-a-testing-mail-server-using-php-on-mac-os-x/).