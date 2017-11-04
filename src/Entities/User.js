import Nymph from "Nymph";
import Entity from "NymphEntity";

export default class User extends Entity {

  // === Static Properties ===

  static etype = "tilmeld_user";
  // The name of the server class
  static class = "Tilmeld\\Entities\\User";

  // === Constructor ===

  constructor(id) {
    super(id);
    this.data.enabled = true;
    this.data.abilities = [];
    this.data.groups = [];
    this.data.inheritAbilities = true;
    this.data.addressType = 'us';
  }

  // === Instance Methods ===

  checkUsername(...args) {
    return this.serverCall('checkUsername', args);
  }

  checkEmail(...args) {
    return this.serverCall('checkEmail', args);
  }

  checkPhone(...args) {
    return this.serverCall('checkPhone', args);
  }

  getAvatar(...args) {
    return this.serverCall('getAvatar', args);
  }

  register(...args) {
    return this.serverCall('register', args);
  }

  logout(...args) {
    return this.serverCall('logout', args);
  }

  login(...args) {
    return this.serverCall('login', args);
  }

  gatekeeper(...args) {
    return this.serverCall('gatekeeper', args);
  }

  recover(...args) {
    return this.serverCall('recover', args);
  }

  // === Static Methods ===

  static current(returnObjectIfNotExist) {
    return User.serverCallStatic('current', [returnObjectIfNotExist]);
  }

  static getClientConfig(...args) {
    return User.serverCallStatic('getClientConfig', args);
  }
}

Nymph.setEntityClass(User.class, User);
export {User};
