import Nymph from "Nymph";
import Entity from "NymphEntity";

export default class User extends Entity {

  // === Static Properties ===

  static etype = "tilmeld_user";
  // The name of the server class
  static class = "Tilmeld\\Entities\\User";
  static registerCallbacks = [];
  static loginCallbacks = [];
  static logoutCallbacks = [];

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
    return this.serverCall('register', args).then((data) => {
      if (data.result) {
        for (const callback of User.registerCallbacks) {
          callback(this);
        }
      }
      if (data.loggedin) {
        for (const callback of User.loginCallbacks) {
          callback(this);
        }
      }
      return Promise.resolve(data);
    });
  }

  logout(...args) {
    return this.serverCall('logout', args).then((data) => {
      if (data.result) {
        for (const callback of User.logoutCallbacks) {
          callback();
        }
      }
      return Promise.resolve(data);
    });
  }

  gatekeeper(...args) {
    return this.serverCall('gatekeeper', args);
  }

  changePassword(...args) {
    return this.serverCall('changePassword', args);
  }

  // === Static Methods ===

  static byUsername(username) {
    return Nymph.getEntity(
      {'class': User.class},
      {'type': '&',
        'strict': ['username', username]
      }
    );
  }

  static current(...args) {
    return User.serverCallStatic('current', args).then((data) => {
      if (data) {
        const user = new User();
        user.init(data)
        data = user;
      }
      return Promise.resolve(data);
    });
  }

  static loginUser(...args) {
    return User.serverCallStatic('loginUser', args).then((data) => {
      if (data.user) {
        const user = new User();
        user.init(data.user)
        data.user = user;
      }
      if (data.result) {
        for (const callback of User.loginCallbacks) {
          callback(data.user);
        }
      }
      return Promise.resolve(data);
    });
  }

  static sendRecoveryLink(...args) {
    return User.serverCallStatic('sendRecoveryLink', args);
  }

  static recover(...args) {
    return User.serverCallStatic('recover', args);
  }

  static getClientConfig(...args) {
    return User.serverCallStatic('getClientConfig', args);
  }

  static on(eventType, callback) {
    if (eventType === 'register') {
      User.registerCallbacks.push(callback);
    } else if (eventType === 'login') {
      User.loginCallbacks.push(callback);
    } else if (eventType === 'logout') {
      User.logoutCallbacks.push(callback);
    }
  }
}

Nymph.setEntityClass(User.class, User);
export {User};
