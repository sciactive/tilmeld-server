import Nymph from "Nymph";
import Entity from "NymphEntity";

export default class Group extends Entity {

  // === Static Properties ===

  static etype = "tilmeld_group";
  // The name of the server class
  static class = "Tilmeld\\Entities\\Group";

  // === Constructor ===

  constructor(id) {
    super(id);
    this.data.enabled = true;
    this.data.abilities = [];
    this.data.addressType = 'us';
  }

  // === Instance Methods ===

  checkGroupname(...args) {
    return this.serverCall('checkGroupname', args);
  }

  checkEmail(...args) {
    return this.serverCall('checkEmail', args);
  }

  getAvatar(...args) {
    return this.serverCall('getAvatar', args);
  }

  getChildren(...args) {
    return this.serverCall('getChildren', args);
  }

  getDescendants(...args) {
    return this.serverCall('getDescendants', args);
  }

  getLevel(...args) {
    return this.serverCall('getLevel', args);
  }

  isDescendant(...args) {
    return this.serverCall('isDescendant', args);
  }

  // === Static Methods ===

  static getPrimaryGroups(...args) {
    return Group.serverCallStatic('getPrimaryGroups', args);
  }

  static getSecondaryGroups(...args) {
    return Group.serverCallStatic('getSecondaryGroups', args);
  }
}

Nymph.setEntityClass(Group.class, Group);
export {Group};
