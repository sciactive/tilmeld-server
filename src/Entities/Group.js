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
    return this.serverCall('checkGroupname', args, true);
  }

  checkEmail(...args) {
    return this.serverCall('checkEmail', args, true);
  }

  getAvatar(...args) {
    return this.serverCall('getAvatar', args, true);
  }

  getChildren(...args) {
    return this.serverCall('getChildren', args, true);
  }

  getDescendants(...args) {
    return this.serverCall('getDescendants', args, true);
  }

  getLevel(...args) {
    return this.serverCall('getLevel', args, true);
  }

  isDescendant(...args) {
    return this.serverCall('isDescendant', args, true);
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
