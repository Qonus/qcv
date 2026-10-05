from odoo import fields, models

class Position(models.Model):
    _name = 'qcv.position'
    _description = 'Imported position'
    _order = 'title'

    title = fields.Char(required=True)
    source_id = fields.Integer(index=True)
    cv_count = fields.Integer()
    imported_at = fields.Datetime()
    attribute_ids = fields.One2many('qcv.position.attribute', 'position_id')

class PositionAttribute(models.Model):
    _name = 'qcv.position.attribute'
    _description = 'Position attribute with aggregates'
    _order = 'id'

    position_id = fields.Many2one('qcv.position', required=True, ondelete='cascade')
    title = fields.Char(required=True)
    attr_type = fields.Char(string='Type')
    answers_count = fields.Integer()
    avg_value = fields.Float()
    min_value = fields.Float()
    max_value = fields.Float()
    top_values = fields.Char()