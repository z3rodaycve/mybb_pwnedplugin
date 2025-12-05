<h1 align="center"><br><br>[MyBB] HIBP Password Check Plugin <sup>v1.0.0</sup><br></h1>

<h4 align="center">Password Security, Data Breach Lookup</h4>

<p align="center">
  <a href="https://mybb.com/download/">
    <img alt="Download MyBB" src="https://img.shields.io/badge/MyBB%E2%80%8E%20Forum%E2%80%8E%20Software-Download-0?style=flat&labelColor=%23066dd1&color=%235caeff">
  </a>
  <a href="https://haveibeenpwned.com/">
     <img alt="HaveIBeenPwned" src="https://img.shields.io/badge/HaveIBeenPwned-visit-0?style=flat&labelColor=%23066dd1&color=%235caeff">
  </a>
</p>

# Overview
HIBP Password Check is a opensource MyBB plugin that **allows** you to **check user's registration password** input against **HIBP** (haveibeenpwned.com) database. 
<br><br> You **do not** need any kind of API key, as **HIBP** provides **completely free password lookup service** (at least for now).

# Installation

[Installation](#howtoinstall) is **simple**, to install **HIBP Password Check Plugin** you need to:

1. *Copy the repository* / *Download the MyBB plugin* under **releases** (or from official [MyBB extend site](placeholder)), and **extract it** (as the file is in .zip format)
2. Copy the contents of `pwnedplugin.css`, and create a new **Theme Stylesheet** *(not required if you do not want to show the HaveIBeenPwned tooltip)*
3. **Install** and **activate** the MyBB plugin!
  
If you need help with installing HIBP Password Check Plugin, feel free to contact me anytime using any of the contact details listed on my [GitHub profile](https://github.com/z3rodaycve).

# Future roadmap / To-do List
- [ ] Add an option to only warn the user, instead of both warning them and blocking submission of a breached/vulnerable password.
- [ ] Make the HaveIBeenPwned tooltip (logo) customizable. => logo icon, description

# License

HIBP Password Check Plugin is released under the [GNU GPL v3](https://www.gnu.org/licenses/gpl-3.0.en.html) license. <br><br>
HIBP Password Check Plugin uses [MyBB Opensource Forum Software](https://mybb.com/). <br>
HIBP Password Check Plugin uses [HaveIBeenPwned](https://haveibeenpwned.com/) for password lookups.
