<div class="modal" id="userEditDelOptionsModal" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
      	<button type="button" class="close closeEditDelOptionsModal hide" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title"><strong>Confirmation</strong></h4>
      </div>

      <div class="modal-body">
      	<input type="hidden" class="action_type">
      	<input type="hidden" class="change_type">

      	<div class="steps_area">
	      	<div class="step1">
		      	<div class="form-group">
					<label class="control-label">
			            <input type="radio" id="del_all" class="selectDelType" value="del_all" name="del_type">
						Delete all child accounts.
					</label>
				</div>

				<div class="form-group">
					<label class="control-label">
			            <input type="radio" class="selectDelType" value="shift_all" name="del_type">
						Do not delete child accounts, shift them to the parent account.
					</label>
				</div>
			</div>

			<div class="step2 hide">
		      	<div class="form-group">
					<label class="control-label">
			            <input checked="checked" class="selectShiftType" type="radio" value="parent_account" name="shift_type">
						Shift all devices from child accounts to "Unassigned Devices" of parent account
					</label>
				</div>

				<div class="form-group">
					<label class="control-label">
			            <input class="selectShiftType" type="radio" value="own_parent_account" name="shift_type">
						Shift all unassigned devices & devices from child accounts to "Unassigned Devices" of parent account
					</label>
				</div>

				<div class="form-group">
					<label class="control-label">
			            <input type="radio" class="selectShiftType" value="same_acc" name="shift_type">
						Shift device from child accounts as assigned devices in same
					</label>
				</div>
			</div>
		</div>

		<div class="just_confirm text-center hide">
			Are you sure you want to delete this?
		</div>

      </div>

      <div class="modal-footer">
      	<button class="btn btn-primary btn-flat backToStep1 hide">Back</button>
      	<button class="btn btn-primary btn-flat submitEditDelUserOptions hide">Submit</button>

      </div>


  </div>
</div>
</div>