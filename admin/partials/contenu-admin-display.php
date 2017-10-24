<?php

/**
 * Provide a admin area view for the plugin.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap contenu-wrap" ng-app="contenu">
	<div ng-controller="TypeBuilder">

		<div class="row">
			<div class="notice below-h2 {{notice.style}}" ng-show="notice.visible" style="position:relative;">
				<p>{{notice.content}}</p>
				<button type="button" class="notice-dismiss" ng-click="dismissNotice()"><span class="screen-reader-text">Dismiss this notice.</span></button>
			</div>
		</div>

		<div class="row">
			<div class="type-column meta-box-sortables">
				<h1>Types <a href="#" class="page-title-action" ng-click="addType()">Add New</a></h1>
			</div>
			<div class="field-column meta-box-sortables" ng-show="selectedType">
				<h1>{{selectedType.name}}'s Fields <a href="#" class="page-title-action" ng-click="addField()">Add New</a></h1>
			</div>
		</div>

		<div class="row">
			<div id="contenu-columns-wrap">
				<div class="metabox-holder">
					<div class="type-column meta-box-sortables" ui-sortable="sortableOptions" ng-model="types">
						<div class="postbox" ng-controller="TypeBox" ng-class="{closed: type.$$collapsed, unsortable: !type.$$saved}" ng-repeat="type in types">
							<button type="button" class="handlediv button-link" aria-expanded="false" ng-click="collapse()"><span class="screen-reader-text">Toggle panel: At a Glance</span><span class="toggle-indicator" aria-hidden="true"></span></button>
							<h3 class="hndle">
								<span>{{typeName()}}</span>
							</h3>
							<div class="inside">

								<form class="initial-form hide-if-no-js">

									<div class="field-wrap">
										<label>Name</label>
										<input type="text" name="post_title" autocomplete="off" ng-model="type.name">
									</div>

									<div class="field-wrap">
										<label>Plural Form</label>
										<input type="text" name="post_plural" autocomplete="off" ng-model="type.plural">
									</div>

									<div class="field-wrap">
										<label>Description</label>
										<textarea name="post_description" rows="3" cols="15" autocomplete="off" ng-model="type.description"></textarea>
									</div>

									<div class="field-wrap">
										<label><input type="checkbox" name="field_form_value" ng-model="type.single"> Use radios. <i>(Form use only)</i></label>
									</div>

									<div class="field-wrap" ng-show="type.$$saved">
										<label>Shortcode</label>
										<input type="text" name="post_shortcode" autocomplete="off" value="{{typeShortcode()}}" readonly>
									</div>

									<p class="submit">
										<button class="button button-primary" ng-click="saveType()">Save</button>
										<a class="submitdelete deletion" ng-show="type.$$saved" open-dialog>Delete</a>
										<br class="clear">
									</p>

									<div id="dialog-confirm-{{type.id}}" title="Are you sure?" ng-show="type.$$saved">
									  <p><span class="ui-icon ui-icon-alert"></span>This will permenantly delete this type from WordPress.</p>
									</div>

								</form>
							</div>
						</div>
					</div>
					<div class="field-column meta-box-sortables" ui-sortable="fieldSortableOptions" ng-model="selectedType.fields">
						<div class="postbox" ng-controller="FieldBox" ng-class="{closed: collapsed}" ng-repeat="field in selectedType.fields">
							<button type="button" class="handlediv button-link" aria-expanded="false" ng-click="collapse()"><span class="screen-reader-text">Toggle panel: At a Glance</span><span class="toggle-indicator" aria-hidden="true"></span></button>
							<h3 class="hndle ui-sortable-handle">
								<span>{{fieldName()}}</span>
							</h3>
							<div class="inside">

								<form class="initial-form hide-if-no-js">

									<div class="field-wrap">
										<label>Name</label>
										<input type="text" name="field_title" autocomplete="off" ng-model="field.name">
									</div>

									<div class="field-wrap">
										<label><input type="checkbox" name="field_private" ng-model="field.private"> Hide field from frontend.</label>
									</div>

									<div class="field-wrap">
										<label><input type="checkbox" name="field_form_value" ng-model="field.value" ng-click="selectValue()"> Use this field as value. <i>(Form use only)</i></label>
									</div>

									<div class="field-wrap">
										<label>Width</label>
										<input type="number" name="field_width" autocomplete="off" ng-model="field.width">
									</div>

									<div class="field-wrap">
										<label>Description</label>
										<input type="text" name="field_description" autocomplete="off" ng-model="field.description">
									</div>

									<div class="field-wrap" ng-show="field.private">
										<label>Add to another field.</label>
										<select ng-model="field.combined" name="field_combine" autocomplete="off">
											<option ng-repeat="fieldr in selectedType.fields | filter:{ private: false }" value="{{fieldr.name}}">{{fieldr.name}}</option>
										</select>
									</div>

									<div class="field-wrap" ng-show="field.combined">
										<label>What's the relation?</label>
										<select ng-model="field.relation" name="field_relation" autocomplete="off">
											<option value="additional">Additional Content</option>
											<option value="link">Link</option>
										</select>
									</div>

									<div class="field-wrap">
										<label>Type</label>
										<select ng-model="field.type" name="field_type" autocomplete="off">
											<option value="text"><?php echo esc_html( __( 'Text', 'contenu' ) ); ?></option>
											<option value="text_small"><?php echo esc_html( __( 'Text Small', 'contenu' ) ); ?></option>
											<option value="text_medium"><?php echo esc_html( __( 'Text Medium', 'contenu' ) ); ?></option>
											<option value="text_email"><?php echo esc_html( __( 'Text Email', 'contenu' ) ); ?></option>
											<option value="text_url"><?php echo esc_html( __( 'Text URL', 'contenu' ) ); ?></option>
											<option value="text_money"><?php echo esc_html( __( 'Text Money', 'contenu' ) ); ?></option>
											<option value="textarea"><?php echo esc_html( __( 'Text Area', 'contenu' ) ); ?></option>
											<option value="textarea_small"><?php echo esc_html( __( 'Text Area Small', 'contenu' ) ); ?></option>
											<option value="textarea_code"><?php echo esc_html( __( 'Text Area Code', 'contenu' ) ); ?></option>
											<option value="text_time"><?php echo esc_html( __( 'Time Picker', 'contenu' ) ); ?></option>
											<option value="select_timezone"><?php echo esc_html( __( 'Time Zone Dropdown', 'contenu' ) ); ?></option>
											<option value="select_date_timestamp"><?php echo esc_html( __( 'Date Picker', 'contenu' ) ); ?></option>
											<option value="select_datetime_timestamp"><?php echo esc_html( __( 'Date Time Picker Combo', 'contenu' ) ); ?></option>
											<option value="colorpicker"><?php echo esc_html( __( 'Colorpicker', 'contenu' ) ); ?></option>
											<option value="radio"><?php echo esc_html( __( 'Radio', 'contenu' ) ); ?></option>
											<option value="radio_inline"><?php echo esc_html( __( 'Radio Inline', 'contenu' ) ); ?></option>
											<option value="checkbox"><?php echo esc_html( __( 'Checkbox', 'contenu' ) ); ?></option>
											<option value="multicheckbox"><?php echo esc_html( __( 'Multi Checkbox', 'contenu' ) ); ?></option>
											<option value="multicheckbox_inline"><?php echo esc_html( __( 'Multi Checkbox Inline', 'contenu' ) ); ?></option>
											<option value="select"><?php echo esc_html( __( 'Select', 'contenu' ) ); ?></option>
											<option value="wysiwyg"><?php echo esc_html( __( 'WYSIWYG', 'contenu' ) ); ?></option>
											<option value="file"><?php echo esc_html( __( 'File', 'contenu' ) ); ?></option>
										</select>
									</div>

									<div class="field-wrap" ng-show="hasOptions()">
										<label>Options</label>
										<div class="options-group">
											<div class="options-row" ng-repeat="option in field.options">
												<div class="options-name">
													<input type="text" name="field_option" autocomplete="off" ng-model="option.name">
												</div>
												<div class="options-remove">
													<button class="button button-secondary" ng-click="removeOption($index)">Remove</button>
												</div>
											</div>
										</div>
										<div class="add-option">
											<button class="button button-secondary" ng-click="addOption()">Add Option</button>
										</div>
									</div>

									<p class="submit">
										<a class="submitdelete deletion" ng-click="deleteField($index)">Delete</a>
										<br class="clear">
									</p>

								</form>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>
